<?php

namespace Drupal\metsis_timeseries\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * Class MetsisTsForm.
 */
class MetsisTsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_ts_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //Get the calling page
    // Get the query params from the request
    $query_params = \Drupal::request()->query->all();
    $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_params);
    $calling_results_page = $params['calling_results_page'];
    //$calling_results_page = isset($_GET['calling_results_page']) ? \Drupal\Component\Utility\Html::escape($_GET['calling_results_page']) : '';

    $request = \Drupal::request();
    $referer = $request->headers->get('referer');

    global $metsis_conf;
    $metadata_identifier = isset($_GET['metadata_identifier']) ? \Drupal\Component\Utility\Html::escape($_GET['metadata_identifier']) : '';
    $form = array();

    /* Wrapper for the download url returned by the timeseries service */
    if ($form_state->get('storage') != NULL) {
        $plotURL = $form_state->get('storage');
        $form['show_plot'] = array(
          '#prefix' => '<div class="tsbox tscontent">',
          '#markup' => '<img src=' . $plotURL . '></img>',
          '#suffix' => '</div>',
        );
      }
    $odv_standard_names = adc_get_odv_standard_names(adc_get_odv_object(adc_get_od_variables($metadata_identifier, SOLR_CORE_PARENT)['data']['findAllVariables']), $metsis_conf['ts_exclude_variables']);
    ksort($odv_standard_names);
    if (adc_has_feature_type($metadata_identifier, 'timeSeries') === 1 || in_array('time', $odv_standard_names)) {
      $default_x_axis = 'time';
    }
    else {
      $default_x_axis = '';
    } if (in_array('air_temperature', $odv_standard_names)) {
      $default_y_axis = 'air_temperature';
    }
    else {
      $default_y_axis = '';
    } if (defined('TS_PLOT_NPOINTS')) {
      $default_ts_plot_npoints = TS_PLOT_NPOINTS;
    }
    else {
      $default_ts_plot_npoints = '';
    } $form['opendap_uri'] = array('#type' => 'hidden', '#disabled' => true, '#default_value' => adc_get_data_access_resource(SOLR_CORE_PARENT, $metadata_identifier)['OPeNDAP']['uri'],);
    $form['metadata_identifier'] = array('#type' => 'hidden', '#disabled' => true, '#default_value' => $metadata_identifier,);
    $form['ts_plot_npoints'] = array('#type' => 'textfield', '#default_value' => $default_ts_plot_npoints, '#size' => 7,);
    $form['ts_plot_file_format'] = array('#type' => 'select', '#options' => array('png' => 'PNG', 'svg' => 'SVG',), '#default_value' => 'png', '#description' => t(''), '#empty' => t(''),);
    $form['x_axis'] = array('#type' => 'select', '#options' => $odv_standard_names, '#default_value' => $default_x_axis, '#description' => t(''), '#empty' => t(''),);
    $form['y_axis'] = array('#type' => 'select', '#options' => $odv_standard_names, '#default_value' => $default_y_axis, '#description' => t(''), '#empty' => t(''),);
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
      //'#suffix' => '</div>',
      //'#submit' => array('metsis_timeseries_submit'),
    );

/**
 * Create back to results link
 */
 /*if(is_int($params['calling_results_page'])) {
 $url = Url::fromRoute('metsis_qsearch.qsearch_results_form', [
   'page' => $params['calling_results_page'],
 ], ['absolute' => TRUE]);
 $calling_results_page = $url->toString();
    //Reender back to results button
}*/
    $form['back_to_results'] = array(
      '#prefix' => '<div class="csvbox contentc">',
      '#markup' => "<a href=" . $calling_results_page .' class="adc-button">' . $this->t('Back to results') . "</a>",
      '#suffix' => '</div>'
    );

    $form['#attached']['library'][] = 'metsis_timeseries/responsive';
    $form['#attached']['library'][] = 'metsis_lib/adc_buttons';

    return $form;
  }




  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $receipt = $this->adc_get_ts_query($form_state);
    $results[] = array($receipt['wps_ProcessOutputs']['wps_Output']['wps_Data']['wps_LiteralData']);
    //var_dump($form);
    $form_state->set('storage', $results[0][0][0]);
    $form_state->setRebuild();
  }


  function adc_get_ts_query($form_state) {
    $req_params = array('ServiceProvider' => TS_SERVICE_PROVIDER, 'metapath' => TS_METAPATH, 'Service' => TS_SERVICE_NAME, 'Request' => TS_REQUEST, 'Version' => TS_WPS_VERSION, 'Identifier' => TS_IDENTIFIER, 'datainputs' => $this->adc_get_ts_datainputs($form_state),);
    $built_query = http_build_query($req_params);
    //var_dump($built_query);
    return adcwps_query(TS_SERVER_PROTOCOL, TS_SERVER, TS_SERVICE_PATH, $built_query);
  }

  function adc_get_ts_datainputs($form_state) {
    $datainputs_array = array('xvar' => $form_state->getValue('x_axis'), 'yvar' => $form_state->getValue('y_axis'), 'everyNth' => $form_state->getValue('ts_plot_npoints'), 'fileFormat' => $form_state->getValue('ts_plot_file_format'), 'fileName' => adc_get_random_file_name(), 'odurl' => $form_state->getValue('opendap_uri'),);
    $tmp_datainputs = [];
    foreach ($datainputs_array as $k => $v) {
      array_push($tmp_datainputs, $k . "=" . $v);
    } return implode(";", $tmp_datainputs);
  }
}

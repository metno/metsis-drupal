<?php

namespace Drupal\metsis_csv\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\node\Entity\Node;
use Drupal\Core\Url;
/**
 * Class MetsisCsvhForm.
 */
class MetsisCsvForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_csv_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    //Get the calling page
    // Get the query params from the request
    $query_params = \Drupal::request()->query->all();
    $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_params);
    $calling_results_page = (int) $params['calling_results_page'];
    //$calling_results_page = isset($_GET['calling_results_page']) ? \Drupal\Component\Utility\Html::escape($_GET['calling_results_page']) : '';

    /* Get the node id of the ascii download basic page and add the body to this form
     * TODO: Check if we can render the page here. Or use blocks as in version 7
     */
/*
    $nids = \Drupal::entityQuery('node')
        ->condition('title', 'ASCII data download')
        ->sort('nid', 'DESC')
        ->execute();

    $mypage = \Drupal\node\Entity\Node::load($nids);
    $form['page'] = array('#markup' => $mypage->getBody());
*/
    global $metsis_conf;
    $metadata_identifier = isset($_GET['metadata_identifier']) ? \Drupal\Component\Utility\Html::escape($_GET['metadata_identifier']) : '';
    \Drupal::logger('metsis_csv')->debug('Calling metsis csv form with metadata_identifier: ' . $metadata_identifier);
    if (defined('CSV_NPOINTS')) {
      $default_csv_npoints = CSV_NPOINTS;
    } $form = array();

    /* Wrapper for the download url returned by the csv service */
    if ($form_state->get('results') != NULL) {
        $CSVFileURL = $form_state->get('results')[0][0][0];
        $form['show_dl_link'] = array(
          '#prefix' => '<div class="csvbox feedback">',
          '#markup' => '<div><a href="' .  $CSVFileURL . '" download="filename">You can download your data by following this link.</a></div>',
          '#suffix' => '</div>',
        );
    }
    $odv_object = adc_get_odv_object(adc_get_od_variables($metadata_identifier, SOLR_CORE_PARENT)['data']['findAllVariables']);
    $form['od_variables'] = array('#type' => 'container',);
    $header = array('standard_name' => t('Standard name'), 'units' => t('Units'),);
    foreach ($odv_object as $odvo) {
      if (key_exists('standard_name', $odvo)) {
        if (in_array(trim($odvo['standard_name']), $metsis_conf['csv_exclude_variables'])) {
          continue;
        } $options[$odvo['standard_name']] = array('standard_name' => $odvo['standard_name'], 'units' => $odvo['units'],);
      }
    } ksort($options);
    $form['od_variables_tabular'] = array(
      '#type' => 'container',
      '#prefix' => '<div class="csvbox contenta">',
      '#suffix' => '</div>'
    );
    $form['od_variables_tabular']['selected_variables'] = array('#type' => 'tableselect', '#header' => $header, '#options' => $options, '#attributes' => array('class' => array('csv-vars-table')),);
    $form['opendap_uri'] = array('#type' => 'hidden', '#disabled' => true, '#default_value' => adc_get_data_access_resource(SOLR_CORE_PARENT, $metadata_identifier)['OPeNDAP']['uri'],);
    $form['csv_npoints'] = array('#type' => 'hidden', '#default_value' => $default_csv_npoints, '#size' => 7,);
    $form['csv_file_format'] = array(
      '#type' => 'select',
      '#options' => array(
        'csv' => 'CSV',
      ),
      '#default_value' => 'csv',
      '#description' => t(''),
      '#empty' => t(''),
      '#prefix' => '<div class="csvbox contentb"> Output format: ',
      '#suffix' => '</div>'
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#prefix' => '<div class="csvbox contentc">',
      '#suffix' => '</div>',
      //'#submit' => array('metsis_csv_submit'),
      /* TODO: Implement ajax callback for smoother ui experience? */
    );
    $form['#validate'][] = 'mcsv_var_select_validate';

    /**
     * Create back to results link
     */
     $url = Url::fromRoute('metsis_qsearch.qsearch_results_form', [
       'page' => $calling_results_page,
     ], ['absolute' => TRUE]);
     $target_url = $url->toString();
    //Reender back to results button
    $form['back_to_results'] = array(
      '#prefix' => '<div class="csvbox contentc">',
      '#markup' => "<a href=" . $target_url .' class="adc-button">' . $this->t('Back to results') . "</a>",
      '#suffix' => '</div>'
    );

    //Add style libraries
    //$form['#attached']['library'][] = 'metsis_lib/metsis_lib';
    $form['#attached']['library'][] = 'metsis_csv/responsive';
    $form['#attached']['library'][] = 'metsis_lib/tables';
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
    $receipt = $this->adc_get_csv_query($form_state);
    foreach ($receipt['wps_ProcessOutputs']['wps_Output'] as $wpsPO) {
      if (in_array_r('CSVOutputFileURL', $wpsPO)) {
        $results[] = array($wpsPO['wps_Data']['wps_LiteralData']);
      }
    } $results[] = array("vars passed in from form to form");
    //var_dump($results[0][0][0]);
    $form_state->set('results', $results);
    $form_state->setRebuild();
  }


  function adc_csv_wps_query($server, $port, $service, $req_params) {
    $con = new HttpConnection($server, $port);
    $res = $con->get($service, $req_params);
    $res_body = hack_xml_namespace($res['body']);
    $res_body_xml = new SimpleXMLElement($res_body);
    $jres_body = \Drupal\Component\Serialization\Json::decode(\Drupal\Component\Serialization\Json::encode($res_body_xml));
    return $jres_body;
  }

  function adc_get_csv_query($form_state) {
    $req_params = array('ServiceProvider' => CSV_SERVICE_PROVIDER, 'metapath' => CSV_METAPATH, 'Service' => CSV_SERVICE_NAME, 'Request' => CSV_REQUEST, 'Version' => CSV_WPS_VERSION, 'Identifier' => CSV_IDENTIFIER, 'datainputs' => $this->adc_get_csv_datainputs($form_state),);
    $built_query = http_build_query($req_params);
    return adcwps_query(CSV_SERVER_PROTOCOL, CSV_SERVER, CSV_SERVICE_PATH, $built_query);
  }

  function adc_get_csv_datainputs($form_state) {
    $selected_variables = [];
    foreach ($form_state->getValue('selected_variables') as $sv) {
      if ($sv != NULL) {
        array_push($selected_variables, $sv);
      }
    }
    //var_dump($selected_variables);
    $datainputs_array = array(
      'everyNth' => $form_state->getValue('csv_npoints'),
      'fileFormat' => $form_state->getValue('csv_file_format'),
      'varSNList' => implode("+", $selected_variables),
      'fileName' => adc_get_random_file_name(),
      'odurl' => $form_state->getValue('opendap_uri'),
    );
    //var_dump($datainputs_array);
    $tmp_datainputs = [];
    foreach ($datainputs_array as $k => $v) {
      array_push($tmp_datainputs, $k . "=" . $v);
    } return implode(";", $tmp_datainputs);
  }
}

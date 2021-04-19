<?php

namespace Drupal\metsis_elements\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Render\Markup;


require_once drupal_get_path('module', 'metsis_qsearch') . '/metsis_qsearch.constants.inc';
require_once drupal_get_path('module', 'metsis_elements') . '/includes/metsis_elements.constants.inc';
require_once drupal_get_path('module', 'metsis_elements') . '/includes/metsis_elements.utils.inc';
/**
 * Class MetsisElementsForm
 */
class MetsisElementsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_elements_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    global $metsis_conf;
    global $base_url;

    /**
     * Get the query parameters from the calling pre_page
     */
    $query_from_request = \Drupal::request()->query->all();
    $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
    $calling_results_page = $params['calling_results_page'];
    metsis_set_session_var('calling_results_page', $params['calling_results_page']);
    $page_number =  (int) $params['page'];
    $metadata_identifier = $params['metadata_identifier'];
    $solr_parent = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_PARENT, array($metadata_identifier), array('mmd_related_dataset'), 0, 1);
    $metadata_identifier_array = $solr_parent['response']['docs'][0]['mmd_related_dataset'];
    $number_of_children = count($metadata_identifier_array);
    $number_results_per_page = RESULTS_PER_PAGE;
    $number_results_found = $number_of_children;
    $number_of_pages = (int) ceil($number_results_found / $number_results_per_page);
    $start_row = ($page_number - 1) * $number_results_per_page;
    $_SESSION['elements']['number_of_pages'] = $number_of_pages;
    $fields_array = explode(',', REQUIRED_CHILD_METADATA);
    $solr_obj = adc_get_datasets_fields_reverse_lookup(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_CHILD, $metadata_identifier, $fields_array, $start_row, $number_results_per_page);
    $rso = reshape_solr_obj($solr_obj);
    $rsok = array_keys($rso);
    $thumbnails = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_MAP_THUMBNAILS, $rsok, array(METADATA_PREFIX . 'metadata_identifier', 'thumbnail_data'), 0, 1000000000);
    $thumbnail_data_array = [];

    foreach ($thumbnails['response']['docs'] as $doc) {
      $thumbnail_data_array[$doc[METADATA_PREFIX . 'metadata_identifier']] = $doc['thumbnail_data'];
    }
    $pager_markup = Markup::create('<div class="pagination-holder clearfix"><div id="light-pagination" class="pagination light-theme simple-pagination"></div><input type="hidden" name="number_of_pages" value="' . $number_of_pages . '"></div>');
    $header = array();
    if (DATASETNAME_VISIBLE) {
      $header['dataset_name'] = t('Dataset name');
    } if (in_array(METADATA_PREFIX . 'personnel_name', $fields_array)) {
      $header['personnel_name'] = t('PI');
    } if (in_array(METADATA_PREFIX . 'temporal_extent_start_date', $fields_array)) {
      $header['collection_period'] = t('Collection period');
    }
    if (defined('RESULTS_THUMB_COLUMN_HEADER')) {
      $header['results_thumb_column_header'] = t(RESULTS_THUMB_COLUMN_HEADER);
    }
    $metadata_div_counter = 0;
    foreach (array_keys($rso) as $mi) {
      if (isset($rso[$mi][METADATA_PREFIX . 'data_access_resource'])) {
        $dar = adc_parse_data_access_resource($rso[$mi][METADATA_PREFIX . 'data_access_resource']);
      }
      //$dar = adc_parse_data_access_resource($rso[$mi][METADATA_PREFIX . 'data_access_resource']);
      if (isset($dar['HTTP'])) {
        $dar_http = $dar['HTTP']['uri'];
      }
      else {
        $dar_http = '';
      } if (isset($dar['OPeNDAP'])) {
        $dar_opendap = $dar['OPeNDAP']['uri'];
        $dar_opendap .= '.html';
      }
      else {
        $dar_opendap = '';
      } if (isset($dar['OGC WMS'])) {
        $dar_ogc_wms = $dar['OGC WMS']['uri'];
      }
      else {
        $dar_ogc_wms = '';
      }




      $title_kv = adc_get_child_title_kv($rso, $mi);
      if (isset($thumbnail_data_array[$mi])) {
        $thumbnail_data = $thumbnail_data_array[$mi];
      }
      else {
        $thumbnail_data = '';
      }
      $collection_period = trim($rso[$mi][METADATA_PREFIX . 'temporal_extent_start_date'] . ' to ' . $rso[$mi][METADATA_PREFIX . 'temporal_extent_end_date']);
      $personnel_name = trim($rso[$mi][METADATA_PREFIX . 'personnel_name'][0]);
      if (defined('SHOW_METADATA_INLINE') && SHOW_METADATA_INLINE === TRUE) {
        $md_kv = array();
        $md_kv['href'] = metsis_get_metadata_div(adc_get_solr_core(array($mi))[$mi], $mi, $metadata_div_counter);
        $md_kv['display_text'] = defined('SOLR_METADATA_BUTTON_TEXT') ? t(SOLR_METADATA_BUTTON_TEXT) : t('Metadata');
        $dataset_name = adc_get_link_list(array($title_kv['href']), array($title_kv['title'][0]));
        $dataset_name .= $md_kv['href'];
      }
      else {
        $md_kv = adc_get_md_kv('l2', $mi);
        $dataset_name = adc_get_link_list(array($title_kv['href'], $md_kv['href']), array($title_kv['title'][0], $md_kv['display_text']));
      }
      if ($dar_http != '') {
        $dataset_name = adc_get_link_list(array($title_kv['href'], $dar_http, $md_kv['href'],), array($title_kv['title'][0], DAR_HTTP_BUTTON_TEXT, $md_kv['display_text'],));
      } if ($dar_opendap != '') {
        $dataset_name = adc_get_link_list(array($title_kv['href'], $dar_http, $dar_opendap, $md_kv['href'],), array($title_kv['title'][0], DAR_HTTP_BUTTON_TEXT, DAR_OPENDAP_BUTTON_TEXT, $md_kv['display_text'],));
      }
      if ($dar_ogc_wms != '') {
        if (defined('RESULTS_THUMB_COLUMN_HEADER')) {
          $dataset_name = adc_get_link_list(
            array(
              $title_kv['href'],
              $dar_http,
              $dar_opendap,
              $md_kv['href'],
            ),
            array(
              $title_kv['title'][0],
              DAR_HTTP_BUTTON_TEXT,
              DAR_OPENDAP_BUTTON_TEXT,
              $md_kv['display_text'],
            )
          );
          $url = Url::fromRoute('metsis_qsearch.wms', [
            'dataset' => $mi,
            'solr_core' => SOLR_CORE_CHILD,
            'page' => $page_number,
          ], ['absolute' => TRUE]);
          $target_url = $url->toString();
          $thumbnail_markup = adc_get_link_list(
            array(
              //$base_url . '/' . 'metsis/map/wms?dataset=' . $mi . '&solr_core=' . SOLR_CORE_CHILD
              $target_url
            ),
            array(
              '<img src="' . $thumbnail_data . '" alt="OGC WMS">'
            )
          );
        }
        else {
          $url = Url::fromRoute('metsis_qsearch.wms', [
            'dataset' => $mi,
            'solr_core' => SOLR_CORE_CHILD,
            'page' => $page_number,
          ], ['absolute' => TRUE]);
          $target_url = $url->toString();
          $dataset_name = adc_get_link_list(
            array(
              $title_kv['href'],
              $dar_http,
              $dar_opendap,
              $md_kv['href'],
              //$base_url . '/' . 'metsis/map/wms?dataset=' . $mi . '&solr_core=' . SOLR_CORE_CHILD
              $target_url
            ),
            array(
              $title_kv['title'][0],
              DAR_HTTP_BUTTON_TEXT,
              DAR_OPENDAP_BUTTON_TEXT,
              $md_kv['display_text'],
              '<img src="' . $thumbnail_data . '" alt="OGC WMS">',
            )
          );
        }
      }

      if (defined('RESULTS_THUMB_COLUMN_HEADER')) {
        $options[$mi] = array(
          'dataset_name' => Markup::create($dataset_name),
          'personnel_name' => Markup::create($personnel_name),
          'collection_period' => Markup::create($collection_period),
          'results_thumb_column_header' => Markup::create($thumbnail_markup),
        );
      }
      else {
        $options[$mi] = array(
          'dataset_name' => Markup::create($dataset_name),
          'personnel_name' => Markup::create($personnel_name),
          'collection_period' => Markup::create($collection_period),
        );
      }
      $metadata_div_counter += 1;
    }
    $form['table'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#attributes' => array('class' => array('elements-vars-table')),
    );
    $form['adc_buttons_wrapper'] = array(
      '#weight' => 30,
      '#prefix' => defined('ADC_FLOATING_BUTTONS') ? '<div  class="adc-floating-buttons">' : '<div  class="adc-buttons-wrapper">',
      '#suffix' => '</div>',
    );
    $form['adc_buttons_wrapper']['pagination'] = array(
      '#weight' => 20,
      '#markup' => $pager_markup,
      '#prefix' => '<div id="number_of_pages_info_div">',
      '#suffix' => '</div>',
    );
    if (BASKET_ELEMENTS_VISIBLE) {
      if (count($form['table']['#options']) > 0) {
        $form['adc_buttons_wrapper']['add_to_basket'] = array(
          '#weight' => 30,
          '#type' => 'submit',
          '#value' => t('Add to basket'),
          '#submit' => array('adc_elements_add_to_basket'),
        );
      }
    }
    $form['adc_buttons_wrapper']['back_to_search'] = array(
      '#weight' => 30,
      '#type' => 'submit',
      '#value' => t('Back to search'),
      '#submit' => array([$this, 'goBackToSearch']),
      '#validate' => array(),
      '#attributes' => array(
        'class' => array(
          'adc-button-small',
        ),
      ),
    );

    $user = \Drupal::currentUser();
    if (($user->id()) && get_user_item_count($user->id()) > 0) {
      $form['adc_buttons_wrapper']['goto_basket'] = array(
        '#weight' => 30,
        '#type' => 'submit',
        '#value' => t('Basket (@basket_item_count)',
          array(
            '@basket_item_count' => get_user_item_count($user->id()))),
        '#submit' => array(
          'adc_goto_basket'
        ),
        '#validate' => array(),
        '#attributes' => array(
          'class' => array(
            'adc-button-small',
          ),
        ),
      );
    }
    $form['results_page'] = array(
      '#type' => 'hidden',
      '#value' => $calling_results_page
    );
    $form['adc_buttons_wrapper']['zzback_to_results'] = array(
      '#weight' => 30,
      '#type' => 'submit',
      '#value' => t('Back to results'),
      '#submit' => array([$this, 'goBackToResults' ]),
      '#validate' => array(),
      '#attributes' => array(
        'class' => array(
          'adc-button-small',
        ),
      ),
    );

    /**
     * Prepare the parent variables for template
     */
    $parent = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_PARENT, array($params['metadata_identifier']), array(METADATA_PREFIX . 'title', METADATA_PREFIX . 'abstract'), 0, 1);
    $form['ptitle'] = [
      '#markup' => $parent['response']['docs'][0][METADATA_PREFIX . 'title'][0],
    ];
    $form['pmid'] = [
      '#markup' => $params['metadata_identifier'],
    ];
    $form['pabstract'] = [
      '#markup' => $parent['response']['docs'][0][METADATA_PREFIX . 'abstract'][0],
    ];


    $form['#attached']['library'][] = 'metsis_qsearch/pagination';
    $form['#attached']['library'][] = 'metsis_qsearch/qsearch.results';
    $form['#attached']['library'][] = 'metsis_lib/tables';
    //$form['#attached']['library'][] = 'metsis_elements/responsive';

    return $form;
    }

/*
    function metsis_elements_submit($form, &$form_state) {
      $form_state["rebuild"] = TRUE;
    }
*/
    function reshape_solr_obj($solr_obj) {
      $solr_obj_reshaped = [];
      foreach ($solr_obj['response']['docs'] as $sodoc) {
        foreach ($sodoc as $k => $v) {
          if ($k !== METADATA_PREFIX . 'metadata_identifier') {
            $solr_obj_reshaped[$sodoc[METADATA_PREFIX . 'metadata_identifier']][$k] = $v;
          }
        }
      }
      return $solr_obj_reshaped;
    }

    function adc_get_child_title_kv($solr_obj, $metadata_identifier) {
      $so = $solr_obj;
      $mi = $metadata_identifier;
      $title_kv = [];
      $title_kv['title'] = $so[$mi][METADATA_PREFIX . 'title'];
      if (isset($so[$mi][METADATA_PREFIX . 'related_information_resource'])) {
        $rir_kv = adc_get_rir_dar_kv($mi, $so[$mi][METADATA_PREFIX . 'related_information_resource']);
        $title_kv['href'] = $rir_kv['Dataset landing page']['uri'];
      }
      elseif (isset($so[$mi][METADATA_PREFIX . 'data_access_resource'])) {
        $dar_kv = adc_get_rir_dar_kv($mi, $so[$mi][METADATA_PREFIX . 'data_access_resource']);
        $title_kv['href'] = $dar_kv['HTTP']['uri'];
      }
      else {
        $title_kv['href'] = "";
      }
      return($title_kv);
    }
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }


  public function goBackToSearch(array &$form, FormStateInterface $form_state) {
    $url = Url::fromRoute('metsis_qsearch.metsis_qsearch_form', [ 'quid' =>  $_SESSION['qsearch']['quid']]);
    //return new RedirectResponse($url);
    //$form_state->setRedirectUrl($url);
    $form_state->setRedirect('metsis_qsearch.metsis_qsearch_form', [ 'quid' =>  $_SESSION['qsearch']['quid']]);
}
public function goBackToResults(array &$form, FormStateInterface $form_state) {
  $calling_results_page = $form_state->getValue('results_page');
  $url = Url::fromRoute('metsis_qsearch.qsearch_results_form', [
    'page' => $calling_results_page,
  ], ['absolute' => TRUE]);
  $target_url = $url->toString();
  //return new RedirectResponse($url);
  //$form_state->setRedirectUrl($url);'
  $form_state->setRedirect('metsis_qsearch.qsearch_results_form', [ 'page' => $calling_results_page ]);
}
}

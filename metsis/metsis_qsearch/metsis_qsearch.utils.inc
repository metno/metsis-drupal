<?php

use \Drupal\metsis_lib\HttpConnection;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Render\Markup;


function q_get_num_res_found($user_query) {
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => $user_query, "start" => 0, "rows" => 1, "wt" => "json",));
  $query_res = json_decode($res['body'], true);
  return $query_res['response']['numFound'];
}

function q_do_search($user_query, $number_of_rows, $start_row) {
  $fields = "id," . METADATA_PREFIX . "personnel_organisation," . METADATA_PREFIX . "abstract," . METADATA_PREFIX . "personnel_name," . METADATA_PREFIX . "title," . METADATA_PREFIX . "temporal_extent_start_date," . METADATA_PREFIX . "temporal_extent_end_date," . METADATA_PREFIX . "personnel_role," . METADATA_PREFIX . "metadata_identifier," . METADATA_PREFIX . "last_metadata_update," . METADATA_PREFIX . "platform_long_name," . METADATA_PREFIX . "cloud_cover_value," . METADATA_PREFIX . "data_center_contact_name," . METADATA_PREFIX . "collection," . METADATA_PREFIX . "geographic_extent_rectangle_east," . METADATA_PREFIX . "geographic_extent_rectangle_south," . METADATA_PREFIX . "geographic_extent_rectangle_west," . METADATA_PREFIX . "geographic_extent_rectangle_north," . METADATA_PREFIX . "data_center_data_center_url," . METADATA_PREFIX . "platform_short_name," . METADATA_PREFIX . "related_information_resource," . METADATA_PREFIX . "project_long_name," . METADATA_PREFIX . "dataset_production_status," . METADATA_PREFIX . "access_constraint," . METADATA_PREFIX . "iso_topic_category," . METADATA_PREFIX . "temporal_extent_start_date," . METADATA_PREFIX . "temporal_extent_end_date," . METADATA_PREFIX . "data_center_data_center_name_long_name," . METADATA_PREFIX . "dataset_language," . METADATA_PREFIX . "data_center_contact_role," . METADATA_PREFIX . "project_short_name," . METADATA_PREFIX . "activity_type," . METADATA_PREFIX . "keywords_keyword," . METADATA_PREFIX . "related_information_type," . METADATA_PREFIX . "operational_status," . METADATA_PREFIX . "instrument_long_name," . METADATA_PREFIX . "data_center_contact_email," . METADATA_PREFIX . "metadata_status," . METADATA_PREFIX . "abstract," . METADATA_PREFIX . "personnel_role," . METADATA_PREFIX . "data_access_description," . METADATA_PREFIX . "data_center_data_center_name_short_name," . METADATA_PREFIX . "data_access_resource," . METADATA_PREFIX . "personnel_email," . "bbox," . METADATA_PREFIX . "data_access_wms_layers_wms_layer," . METADATA_PREFIX . "data_access_type," . METADATA_PREFIX . "related_dataset";
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => $user_query, "sort" => "mmd_temporal_extent_start_date desc", "rows" => $number_of_rows, "start" => $start_row, "wt" => "json", "fl" => $fields,));
  $query_res = json_decode($res['body'], true);
  $counter = 0;
  foreach ($query_res['response']['docs'] as $doc) {
    if (!empty($doc[METADATA_PREFIX . 'data_access_resource'])) {
      $query_res['response']['docs'][$counter][METADATA_PREFIX . 'data_access']['type'] = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
    } $counter = $counter + 1;
  } return $query_res;
}

function q_get_platform_long_name() {
  $obj = metsis_qget_parent_facet_field(METADATA_PREFIX . 'platform_long_name');
  $checkboxes = [];
  while ($current = current($obj['facet_counts']['facet_fields'][METADATA_PREFIX . 'platform_long_name'])) {
    $cstring = (string) $current . '  (' . (string) next($obj['facet_counts']['facet_fields'][METADATA_PREFIX . 'platform_long_name']) . ')';
    $checkboxes[strtolower(str_replace('-', '_', $current))] = $cstring;
    $current = next($obj['facet_counts']['facet_fields'][METADATA_PREFIX . 'platform_long_name']);
  } return $checkboxes;
}

function metsis_qsearch_form_reset($form, &$form_state) {
  //global $base_url;
  //$url = $base_url;
  //$url .= '/';
  //$url .= METSIS_QSEARCH_ENDPOINT;
  $form_state->setRedirect('metsis_qsearch.metsis_qsearch_form');
}

function msb_text_qsearch_validate($element, $form_state) {

}

function msb_get_final_solr_qq($form_state) {
  $qarray = array();
  $q = '*:*';
  if (defined('SEARCH_DEFAULT_METADATA_STATUS')) {
    $qarray[] = (METADATA_PREFIX . 'metadata_status:"' . SEARCH_DEFAULT_METADATA_STATUS . '"');
  }
  else {
    $qarray[] = (METADATA_PREFIX . 'metadata_status:"active"');
  } if (defined('COLLECTIONS')) {
    $qarray[] = (METADATA_PREFIX . 'collection:' . COLLECTIONS . '');
  } if (!empty(adc_get_full_text_search($form_state))) {
    $qarray[] = adc_get_full_text_search($form_state);
  } if (!empty(adc_get_investigator($form_state))) {
    $qarray[] = adc_get_investigator($form_state);
  } if (!empty(msb_get_chosen_topics_and_variables($form_state))) {
    $qarray[] = msb_get_chosen_topics_and_variables($form_state);
  } if (!empty(msb_get_chosen_operational_status($form_state))) {
    $qarray[] = msb_get_chosen_operational_status($form_state);
  } if (!empty(msb_get_chosen_institutions($form_state))) {
    $qarray[] = msb_get_chosen_institutions($form_state);
  } if (!empty(msb_get_chosen_collections($form_state))) {
    $qarray[] = msb_get_chosen_collections($form_state);
  } if (!empty(msb_get_chosen_platform_long_name($form_state))) {
    $qarray[] = msb_get_chosen_platform_long_name($form_state);
  } if (!empty(adc_get_chosen_dates($form_state))) {
    $qarray[] = adc_get_chosen_dates($form_state);
  } if (!empty(adc_get_geographical_search_q($form_state))) {
    $qarray[] = adc_get_geographical_search_q($form_state);
  } if (!empty($qarray)) {
    $q = implode(" AND ", $qarray);
  } return $q;
}

function metsis_qget_parent_facet_field($mmd_metadata_field) {
  $facet_con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $facet_qres = $facet_con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => "*:*", "rows" => 1, "facet" => "true", "facet.field" => $mmd_metadata_field, "wt" => "json",));
  return json_decode($facet_qres['body'], true);
}

function adc_add_to_basket(&$form, &$form_state) {
  $user = \Drupal::currentUser();
  global $metsis_conf;
/*
  if ($metsis_conf['authentication_required'] === 1) {
    adc_require_login(drupal_get_destination(), $metsis_conf['authentication_default_message']);
  }*/
   if (array_filter($form_state->getValue('table'))) {
    $basket_items = array_filter($form_state->getValue('table'));
    $not_basketable = adc_has_related_dataset(SOLR_CORE_PARENT, array_keys($basket_items));
    if (count($not_basketable) > 0) {
      $not_basket = array_values($not_basketable);
      $items =  [
        '#theme' => 'item_list',
        '#list-type' => 'ul',
        '#items' => $not_basket
    ];
    $renderer = \Drupal::service('renderer');
    $html_list = $renderer->render($items);
    $message = 'The following <b>' . count($not_basketable) .
      '</b> are aggregate datasets. To add them to basket choose from their "Child data" ';
    \Drupal::messenger()->addMessage(t($message . $html_list));

      $basket_items = array_diff($basket_items, array_keys($not_basketable));
    } $fields = array("id", "_version_", METADATA_PREFIX . "last_metadata_update", METADATA_PREFIX . "personnel_email", METADATA_PREFIX . "platform_long_name", METADATA_PREFIX . "data_center_contact_name", METADATA_PREFIX . "collection", METADATA_PREFIX . "geographic_extent_rectangle_east", METADATA_PREFIX . "geographic_extent_rectangle_south", METADATA_PREFIX . "geographic_extent_rectangle_west", METADATA_PREFIX . "geographic_extent_rectangle_north", METADATA_PREFIX . "data_center_data_center_url", METADATA_PREFIX . "platform_short_name", METADATA_PREFIX . "related_information_resource", METADATA_PREFIX . "project_long_name", METADATA_PREFIX . "data_access_resource", METADATA_PREFIX . "dataset_production_status", METADATA_PREFIX . "access_constraint", METADATA_PREFIX . "iso_topic_category", METADATA_PREFIX . "temporal_extent_start_date", METADATA_PREFIX . "temporal_extent_end_date", METADATA_PREFIX . "data_center_data_center_name_long_name", METADATA_PREFIX . "dataset_language", METADATA_PREFIX . "data_center_contact_role", METADATA_PREFIX . "data_access_type", METADATA_PREFIX . "project_short_name", METADATA_PREFIX . "abstract", METADATA_PREFIX . "activity_type", METADATA_PREFIX . "keywords_keyword", METADATA_PREFIX . "related_information_type", METADATA_PREFIX . "data_access_wms_layers_wms_layer", METADATA_PREFIX . "operational_status", METADATA_PREFIX . "instrument_long_name", METADATA_PREFIX . "personnel_organisation", METADATA_PREFIX . "data_center_contact_email", METADATA_PREFIX . "instrument_short_name", METADATA_PREFIX . "personnel_role", METADATA_PREFIX . "data_access_description", METADATA_PREFIX . "cloud_cover_value", METADATA_PREFIX . "metadata_identifier", METADATA_PREFIX . "data_center_data_center_name_short_name", METADATA_PREFIX . "metadata_status", METADATA_PREFIX . "personnel_name", METADATA_PREFIX . "title", "bbox",);
    $my_items = array();
    foreach ($basket_items as $bi) {
      $my_items[$bi] = msb_get_fields(SOLR_CORE_PARENT, $bi, $fields);
      foreach ($my_items[$bi] as $mibi) {
        if (isset($mibi['error'])) {
          \Drupal::messenger()->addError($mibi['error']['msg']);
        }
      } foreach ($my_items[$bi]['response']['docs'] as $doc) {
        if (h_adc_has_data_access_resource($doc) == 0) {

        } if (!isset($doc[METADATA_PREFIX . 'data_access_resource'])) {
          $message = $bi . " : " . MISSING_DATA_ACCESS_RESOURCE;
          \Drupal::messenger()->addWarning($message);
          unset($my_items[$bi]);
        }
        else {
          $doc[METADATA_PREFIX . 'data_access_resource'] = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
          $my_items[$bi] = $doc;
        }
      }
    } $basket_query = adc_get_basket_items($user->id(), 'metadata_identifier');
    $basket_query_results = $basket_query->fetchAllAssoc('metadata_identifier');
    $user_basket_content = [];
    foreach ($basket_query_results as $bqr) {
      $user_basket_content[] = $bqr->metadata_identifier;
    } $not_in_basket = array_diff_key($my_items, array_flip($user_basket_content));
    $my_items = $not_in_basket;
    metsis_basket_insert($my_items);
    if (count($my_items) > 0) {

  $my_item_keys =  array_keys($my_items);
  $items =  [
    '#theme' => 'item_list',
    '#list-type' => 'ul',
    '#items' => $my_item_keys
  ];
$renderer = \Drupal::service('renderer');
$html_list = $renderer->render($items);
$message = 'The following <b>' . count($my_items) . '</b> datasets have been added to your basket: ';
 \Drupal::messenger()->addMessage(t($message . $html_list));

    }
    else {
      \Drupal::messenger()->addMessage(t('No new datasets to place in basket.'));
    }
  }
}

function adc_min_select_validate($form, $form_state) {
  if (!array_filter($form_state->getValue('table'))) {
    $form_state->setErrorByName('metsis_qsearch', t('You must choose atleast one item!'));
  }
}

function metsis_map_wms() {
  $query_from_request = \Drupal::request()->query->all();
  $query = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
  if (count($query) > 0) {
    $datasets = explode(",", $query['dataset']);
    $externalURL = $datasets;
    if (isset($query['solr_core'])) {
      $solr_core = $query['solr_core'];
        $page = get_metsis_map_wms_markup($solr_core, $externalURL);
    }
    else {
      $page = get_metsis_map_wms_markup($externalURL);
    }
  }
  //print $page;
  return $page;
}

function adc_get_geographical_search_map() {
  global $metsis_conf;
  global $base_url;
  $search_map_center_lat = $metsis_conf['search_map_center_lat'];
  $search_map_center_lon = $metsis_conf['search_map_center_lon'];
  $search_map_init_zoom = $metsis_conf['search_map_init_zoom'];
  $path_to_bundle = $base_url . '/' . drupal_get_path('module', 'metsis_wms') . '/js/bundle.js';
  $string = <<<EOM
        <div class="map container">
            <div id="map">
        </div></div>
        <script type="text/javascript" src="$path_to_bundle"></script>
        <script type="text/javascript">
        (function ($) {
                  var sClient;
            $(document).ready(function () {
                sClient = mapClient.search({
                    lon: $search_map_center_lon,
                    lat: $search_map_center_lat,
                    zoom: $search_map_init_zoom,
                    bbox: {
                        topleft: {latId: 'edit-bbox-top-left-lat', lonId: 'edit-bbox-top-left-lon'},
                        bottomright: {latId: 'edit-bbox-bottom-right-lat', lonId: 'edit-bbox-bottom-right-lon'}
                          }
                        }
                      );
               //console.log(sClient.polygon);
            });
            })(jQuery);
        </script>

EOM;
  return $string;
}

function adc_get_leveltwo_links($dataset_id, $page_number) {
  $number_of_children = msb_count_children($dataset_id);


  $url = Url::fromRoute('metsis_elements.elements_form', [
    'metadata_identifier' =>  $dataset_id,
    'calling_results_page' => $page_number,
    'page' => 1,
  ], ['absolute' => TRUE]);
  $elements_url = $url->toString();
  //$elements_url = "/" . ELEMENTS_ENDPOINT . "?metadata_identifier=" . $dataset_id;
  //$elements_url .= "&calling_results_page=" . $this_url;
  //$elements_url .= "&page=1";
  if ($number_of_children > 0) {
    $url = '' . '<div class="botton-wrap">' . '<a class="adc-button adc-sbutton" href="' . $elements_url . '" >' . ELEMENTS_BUTTON_TEXT . '..[' . $number_of_children . ']</a>' . '</div>';
    return $url;
  }
}

function msb_count_children($datasetID) {
  $datasetID = '("' . $datasetID . '")';
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => METADATA_PREFIX . "metadata_identifier:$datasetID", "fl" => METADATA_PREFIX . "related_dataset", "rows" => 1, "wt" => "json", "indent" => "true"));
  $body = json_decode($res['body'], true);
  if (isset($body['response']['docs'][0]['mmd_related_dataset'])) {
    return(count($body['response']['docs'][0]['mmd_related_dataset']));
  } return;
}

function get_data_access_markup($dataset_id, $data_access) {
  $url = '' . '<div class="botton-wrap ext_data_source">' . '<a class="adc-button adc-sbutton ext_data_source" href="' . $data_access . '">' . $dataset_id . '</a>' . '</div>';
  return $url;
}

function metsis_qsearch_form_alter(&$form, \Drupal\Core\Form\FormStateInterface &$form_state, $form_id) {
  if ($form_id == 'q_results_tabular_form') {

  }
}

function msb_get_chosen_platform_long_name($form_state) {
  $q = "";
  if (PLATFORM_LONG_NAME_VISIBLE) {
    $sqa = [];
    if (isset($form_state['values']['platform_long_name']['sentinel_1a'])) {
      if (!empty(array_filter($form_state['values']['platform_long_name']['sentinel_1a']['chosen_platform_long_name']))) {
        $s1aqa = [];
        $s1aqa[] = "mmd_platform_long_name:(\"Sentinel-1A\")";
        $s1aqa[] = adc_get_chosen_instrument_modes($form_state, 'sentinel_1a');
        $s1aqa[] = adc_get_chosen_instrument_polarisations($form_state, 'sentinel_1a');
        $sqa[] = "(" . implode(" AND ", array_filter($s1aqa)) . ")";
      }
    } if (isset($form_state['values']['platform_long_name']['sentinel_1b'])) {
      if (!empty(array_filter($form_state['values']['platform_long_name']['sentinel_1b']['chosen_platform_long_name']))) {
        $s1bqa = [];
        $s1bqa[] = "mmd_platform_long_name:(\"Sentinel-1B\")";
        $s1bqa[] = adc_get_chosen_instrument_modes($form_state, 'sentinel_1b');
        $s1bqa[] = adc_get_chosen_instrument_polarisations($form_state, 'sentinel_1b');
        $sqa[] = "(" . implode(" AND ", array_filter($s1bqa)) . ")";
      }
    } if (isset($form_state['values']['platform_long_name']['sentinel_2a'])) {
      if (!empty(array_filter($form_state['values']['platform_long_name']['sentinel_2a']['chosen_platform_long_name']))) {
        $s2aqa = [];
        $s2aqa[] = "mmd_platform_long_name:(\"Sentinel-2A\")";
        $s2aqa[] = adc_get_chosen_product_types($form_state, 'sentinel_2a');
        $s2aqa[] = msb_get_chosen_cloud_cover_value($form_state, 'sentinel_2a');
        $sqa[] = "(" . implode(" AND ", array_filter($s2aqa)) . ")";
      }
    } if (isset($form_state['values']['platform_long_name']['sentinel_2b'])) {
      if (!empty(array_filter($form_state['values']['platform_long_name']['sentinel_2b']['chosen_platform_long_name']))) {
        $s2bqa = [];
        $s2bqa[] = "mmd_platform_long_name:(\"Sentinel-2B\")";
        $s2bqa[] = adc_get_chosen_product_types($form_state, 'sentinel_2b');
        $s2bqa[] = msb_get_chosen_cloud_cover_value($form_state, 'sentinel_2b');
        $sqa[] = "(" . implode(" AND ", array_filter($s2bqa)) . ")";
      }
    } if (implode(" OR ", array_filter($sqa)) !== '') {
      $q = "(" . implode(" OR ", array_filter($sqa)) . ")";
    }
  } return $q;
}

function adc_get_chosen_instrument_modes($form_state, $platform) {
  $q = "";
  $chosen_instrument_modes = array_filter($form_state['values']['platform_long_name'][$platform]['instrument_modes']['chosen_instrument_modes']);
  if (!empty($chosen_instrument_modes)) {
    $q = "mmd_instrument_mode:(\"" . implode("\" OR \"", $chosen_instrument_modes) . "\")";
  } return $q;
}

function adc_get_chosen_product_types($form_state, $platform) {
  $q = "";
  $chosen_product_types = array_filter($form_state['values']['platform_long_name'][$platform]['product_types']['chosen_product_types']);
  if (!empty($chosen_product_types)) {
    $q = "mmd_product_type:(\"" . implode("\" OR \"", $chosen_product_types) . "\")";
  } return $q;
}

function adc_get_chosen_instrument_polarisations($form_state, $platform) {
  $q = "";
  if (INSTRUMENT_POLARISATION_VISIBLE) {
    $chosen_instrument_polarisations = array_filter($form_state['values']['platform_long_name'][$platform]['instrument_polarisations']['chosen_instrument_polarisations']);
    if (!empty($chosen_instrument_polarisations)) {
      $q = "mmd_instrument_polarisation:(\"" . implode("\" OR \"", $chosen_instrument_polarisations) . "\")";
    }
  } return $q;
}

function msb_get_chosen_cloud_cover_value($form_state, $platform) {
  global $metsis_conf;
  $q = "";
  if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
    $chosen_cloud_cover_value = $form_state['values']['platform_long_name'][$platform]['cloud_cover_value']['chosen_cloud_cover_value'];
    if (!empty($chosen_cloud_cover_value)) {
      if ($chosen_cloud_cover_value == ">90%") {
        $q = "mmd_cloud_cover_value:[90 TO *]";
      }
      else {
        $patterns = array();
        $patterns[0] = '/([<])/';
        $patterns[1] = '/(%)/';
        $replacements = array();
        $replacements[0] = '';
        $replacements[1] = '';
        $chosen_cloud_cover_value = preg_replace($patterns, $replacements, $chosen_cloud_cover_value);
        $q = "mmd_cloud_cover_value:[* TO " . $chosen_cloud_cover_value . "]";
      }
    }
  } return $q;
}

function adc_get_chosen_dates(FormStateInterface $form_state) {
  $q = "";
  if (!empty($form_state->getValue('finished_after')) && empty($form_state->getValue('finished_before'))) {
    $finished_after = $form_state->getValue('finished_after');
    $q = "mmd_temporal_extent_start_date:[" . msb_get_iso8601_date_time_zone($finished_after) . " TO *]";
  } if (empty($form_state->getValue('finished_after')) && !empty($form_state->getValue('finished_before'))) {
    $finished_before = $form_state->getValue('finished_before');
    $q = "mmd_temporal_extent_end_date:[* TO " . msb_get_iso8601_date_time_zone($finished_before) . "]";
  } if (!empty($form_state->getValue('finished_after')) && !empty($form_state->getValue('finished_before'))) {
    $finished_after = $form_state->getValue('finished_after');
    $finished_before = $form_state->getValue('finished_before');
    $q = "mmd_temporal_extent_start_date:[" . msb_get_iso8601_date_time_zone($finished_after) . " TO *]" . " AND " . "mmd_temporal_extent_end_date:[* TO " . msb_get_iso8601_date_time_zone($finished_before) . "]";
  } return $q;
}

function adc_get_full_text_search(FormStateInterface $form_state) {
  $q = "";
  $fields = array('full_text',);
  if (!empty($form_state->getValue('chosen_full_text_search'))) {
    $ss = $form_state->getValue('chosen_full_text_search');
    $q .= "(";
    $q .= $ss;
    $q .= ")";
    $qq = array();
    foreach ($fields as $f) {
      $qq[] = $f . ":" . $q;
    } $q = implode(" ", $qq);
  } return $q;
}

function adc_get_geographical_search_q(FormStateInterface $form_state) {
  $q = "";
  $bbox = array();
  if (!empty($form_state->getValue('bbox_top_left_lat'))) {
    $bbox['bbox_top_left_lat'] = $form_state->getValue('bbox_top_left_lat');
  } if (!empty($form_state->getValue('bbox_top_left_lon'))) {
    $bbox['bbox_top_left_lon'] = $form_state->getValue('bbox_top_left_lon');
  } if (!empty($form_state->getValue('bbox_bottom_right_lat'))) {
    $bbox['bbox_bottom_right_lat'] = $form_state->getValue('bbox_bottom_right_lat');
  } if (!empty($form_state->getValue('bbox_bottom_right_lon'))) {
    $bbox['bbox_bottom_right_lon'] = $form_state->getValue('bbox_bottom_right_lon');
  } if ((!empty($bbox['bbox_top_left_lat'])) AND ( !empty($bbox['bbox_bottom_right_lat'])) AND ( !empty($bbox['bbox_bottom_right_lon'])) AND ( !empty($bbox['bbox_top_left_lon']))) {
    $q .= "bbox:";
    $q .= "\"";
    $q .= "Intersects(ENVELOPE(";
    $q .= $bbox['bbox_top_left_lon'];
    $q .= ",";
    $q .= $bbox['bbox_bottom_right_lon'];
    $q .= ",";
    $q .= $bbox['bbox_top_left_lat'];
    $q .= ",";
    $q .= $bbox['bbox_bottom_right_lat'];
    $q .= "))";
    $q .= "\"";
  } return $q;
}

function adc_get_investigator($form_state) {
  $q = "";
  if (INVESTIGATOR_VISIBLE) {
    if (!empty($form_state->getValue('chosen_investigator'))) {
      $investigator = $form_state->getValue('chosen_investigator');
    } if (!empty($investigator)) {
      $q = "(" . METADATA_PREFIX . "personnel_role" . ":" . "Investigator" . " " . "AND" . " " . METADATA_PREFIX . "personnel_name" . ":" . "\"" . $investigator . "\"" . ")";
    }
  } return $q;
}

function adc_get_map_thumb_divs($thumbnail, $target_url) {
  $string = <<<EOD
    <div class = "thumbnail_container">
      <div class = "thumbnail overlay_image">
        <a href = "$target_url">
          <img
            src = "$thumbnail"
           />
        </a>
      </div>
    </div>
EOD;
  return $string;
}

function msb_get_operational_statuses() {
  $obj = msb_get_docs_fields(array(METADATA_PREFIX . 'operational_status'));
  $checkboxes = [];
  foreach ($obj['response']['docs'] as $doc) {
    if (!empty($doc[METADATA_PREFIX . 'operational_status']) && !in_array($doc[METADATA_PREFIX . 'operational_status'], $checkboxes)) {
      $checkboxes[] = $doc[METADATA_PREFIX . 'operational_status'];
    }
  } return $checkboxes;
}

function msb_get_chosen_topics_and_variables($form_state) {
  $q = "";
  $topics_and_variables = array();
  if (!empty($form_state->getValue('chosen_topics_and_variables_a'))) {
    $topics_and_variables[] = $form_state->getValue('chosen_topics_and_variables_a');
  } if (!empty($form_state->getValue('chosen_topics_and_variables_b'))) {
    $topics_and_variables[] = $form_state->getValue('chosen_topics_and_variables_b');
  } if (!empty($topics_and_variables)) {
    $q = "mmd_keywords_keyword:(\"" . implode("\" AND \"", $topics_and_variables) . "\")";
  } return $q;
}

function msb_get_chosen_operational_status($form_state) {
  $q = "";
  $chosen_operational_status = array_filter($form_state->getValue('operational_status')['chosen_operational_status']);
  if (!empty($chosen_operational_status)) {
    if (DEBUG) {

    } $q = METADATA_PREFIX . "operational_status:(\"" . implode("\" OR \"", $chosen_operational_status) . "\")";
  } return $q;
}

function msb_get_children($parent_id) {
  $fields = "id," . METADATA_PREFIX . "personnel_email," . METADATA_PREFIX . "personnel_organisation," . METADATA_PREFIX . "personnel_name," . METADATA_PREFIX . "temporal_extent_start_date," . METADATA_PREFIX . "temporal_extent_end_date," . METADATA_PREFIX . "data_access_resource";
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . SOLR_CORE_CHILD . '/select', array("q" => METADATA_PREFIX . "related_dataset:$parent_id", "rows" => SEARCH_MAXIMUM_ROWS_TO_FETCH, "wt" => "json", "fl" => $fields,));
  $children = json_decode($res['body'], true);
  $children['parent'] = $parent_id;
  return $children;
}

function msb_update_local_mmd_keywords() {
  msb_delete_local_mmd_keywords();
  $phrases = array();
  $mmd_keywords_obj = msb_get_mmd_keywords();
  foreach ($mmd_keywords_obj['response']['docs'] as $doc) {
    foreach ($doc[METADATA_PREFIX . 'keywords_keyword'] as $kw) {
      if (!in_array($kw, $phrases)) {
        $phrases[] = $kw;
      }
    }
  } // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// variable_set(METADATA_PREFIX . 'keywords', $phrases);
\Drupal::state()->set(METADATA_PREFIX . 'keywords', $phrases);

}

function msb_delete_local_mmd_keywords() {
  // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// variable_del(METADATA_PREFIX . 'keywords');
\Drupal::state()->delete(METADATA_PREFIX . 'keywords');

}

function msb_get_mmd_keywords() {
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => "*:*", "wt" => "json", "fl" => METADATA_PREFIX . "keywords_keyword", "indent" => "true"));
  return Json::decode($res['body']);
}

function h_adc_has_data_access_type($solr_doc, $data_access_type) {
  if(isset($solr_doc[METADATA_PREFIX . 'data_access_type'])) {
    if (h_adc_has_data_access_resource($solr_doc) == 1) {
      if (in_array($data_access_type, $solr_doc[METADATA_PREFIX . 'data_access_type'])) {
        return 1;
      }
    }
    else {
      return 0;
    }
  }
}

function msb_get_short_isodate($date) {
  $tmp_date = new DateTime($date);
  $short_isodate = $tmp_date->format('Y-m-d');
  return $short_isodate;
}

function msb_get_iso8601_date_time_zone($date_string) {
  $tmp_date = new DateTime($date_string);
  $iso8601_date_time_zone = $tmp_date->format('Y-m-d\TH:i:s\Z');
  return $iso8601_date_time_zone;
}

function hack_get_skos_opstat($opstat_name) {
  $opstat_definition = "";
  $hack_opstat_array = array("Operational Status" => "Controlled vocabulary to be used in METAMOD context to describe
          operational status of datasets handled. This is used in filtering of the
          information. Typically scientific datasets are generated without a
          temporal perspective and review process. The review process of
          scientific products is publications in refereed journals or in data
          citation papers. Experimental, Pre-Operational and Operational are
          concepts supported by institutions with a committment for a specific
          delivery over time.", 'Operational' => "This is used to tag datasets that are continously being evaluated
          and validated. The production of the data is under the
          responsibility of an authority and follows a review and
          documentation process. Operational data are generated on a
          continous basis with a temporal perspective.", 'Pre-Operational' => "This is used to describe data that are under review for becoming
          the next operational delivery. It is usually produced continously,
          but not necessarily distributed to users not specifically. It
          covers the intermediate step between Experimental and
          Operational.", 'Experimental' => "This is used to describe data that belongs to a development and
          production chain that provides operational data. It is the first
          step in the process of becoming operational.", 'Scientific' => "This is used to describe purely scientific products. that is
          products generated through scientific projects and usually with a
          limited temporal perspective.",);
  if (!empty($hack_opstat_array[$opstat_name])) {
    return $hack_opstat_array[$opstat_name];
  } return $opstat_definition;
}

function msb_get_fields($solr_core, $dataset_id, $fields) {
  $fl = implode(",", $fields);
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . $solr_core . '/select', array("q" => METADATA_PREFIX . "metadata_identifier:" . "\"" . $dataset_id . "\"", "wt" => "json", "fl" => "$fl",));
  return json_decode($res['body'], true);
}

function msb_get_fimex_link($dataset_id, $solr_core, $referer) {
  $url = '';
  $url .= '<div class="botton-wrap">';
  $url .= '<a class="adc-button adc-sbutton" href="' . '/metsis_fimex?dataset_id=' . $dataset_id . '&solr_core=' . $solr_core . '&referer=' . $referer . '" >Transform</a>';
  $url .= '</div>';
  return $url;
}

function adc_keywords_to_string($keywords_array) {
  $glue = "<br>";
  return implode($glue, $keywords_array);
}

function adc_investigator_validate($element, $form_state) {

}

function msb_start_finish_date_validate($element, $form_state) {
  if (!empty($element['#value']['date']) && !msb_date_validate($element['#value']['date'])) {
    form_error($element, t('Not a valid date'));
  }
}

function msb_all_or_none_latlon_validate($form, &$form_state) {
  if (!empty($form_state->getValue('bbox_top_left_lat')) || !empty($form_state->getValue('bbox_top_left_lon')) || !empty($form_state->getValue('bbox_bottom_right_lat')) || !empty($form_state->getValue('bbox_bottom_right_lat'))) {
    if (empty($form_state->getValue('bbox_top_left_lat')) || empty($form_state->getValue('bbox_top_left_lon')) || empty($form_state->getValue('bbox_bottom_right_lat')) || empty($form_state->getValue('bbox_bottom_right_lat'))) {
      form_set_error('geographical_search', t('All <b>OR</b> none of the "Geographical search" values must be set.'));
    }
  }
}

function msb_foo_validate($form, &$form_state) {

}

function adc_goto_basket() {
  $user = \Drupal::currentUser();
  global $metsis_conf;
  if (!isset($metsis_conf['basket_endpoint'])) {
    \Drupal::messenger()->addWarning("No basket view defined. You need to create a basket view and set the variable \$metsis_conf['basket_endpoint'] as administrator first.");
  }
  else {
    //drupal_goto($metsis_conf['basket_endpoint']);
      $response = new RedirectResponse('/basket');
    //$response = new RedirectResponse(Url::fromRoute('view.basket_view.page_1'));
    return $response->send();
   //return new RedirectResponse('view.basket_view.page_1');
  } return;
}

/**
 * @TODO
 * This functionality are moved to the forms needing this functionality. Can be deleted
 */
function adc_go_back_to_search() {

  $url = Url::fromRoute('metsis_qsearch.metsis_qsearch_form', [ 'quid' =>  $_SESSION['qsearch']['quid']]);
  var_dump($url);
  return new RedirectResponse($url);
}

function adc_search_by_mail() {
  global $base_url;
  $sbm_url = 'mailto:?subject=Data search&body=';
  $sbm_url .= $base_url;
  $sbm_url .= '/';
  $sbm_url .= METSIS_QSEARCH_ENDPOINT;
  $sbm_url .= '/?quid=';
  $sbm_url .= $_SESSION['qsearch']['quid'];
  //drupal_goto($sbm_url);
  //return;
  $response = new RedirectResponse($sbm_url);
  return $response->send();
}

function adc_longitude_validate() {
  return;
}

function adc_latitude_validate() {
  return;
}

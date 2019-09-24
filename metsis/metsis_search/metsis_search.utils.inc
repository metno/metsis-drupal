<?php

function msb_get_all_children($form_state) {
  $children = array();
  $parents = msb_do_search($form_state);
  foreach ($parents['response']['docs'] as $doc) {
    $children[$doc['id']] = msb_get_children($doc['id']);
  }
}

function msb_mmd_keywords_autocomplete($string = ' ') {
  $matches = array();
  $mmd_keywords = variable_get(METADATA_PREFIX . 'keywords');
  foreach ($mmd_keywords as $kw) {
    if (stristr($kw, $string)) {
      $matches[$kw] = $kw;
    }
  } drupal_json_output($matches);
}

function msb_date_validate($date, $format = 'Y-m-d') {
  $d = DateTime::createFromFormat($format, $date);
  return $d && $d->format($format) == $date;
}

function msb_get_tds_metadata($tds_uri) {
  $pattern = '/\.html/i';
  $replacement = '.xml';
  $result = drupal_http_request(preg_replace($pattern, $replacement, $tds_uri));
  $data = $result->data;
  $xml = simplexml_load_string($data);
  $json = json_encode($xml);
  $array = json_decode($json, TRUE);
  return json_decode($json, TRUE);
}

function msb_get_tooltip_js() {
  $string = <<<EOM
    <script type="text/javascript">
     $('[msb-tooltip!=""]').qtip({// Grab all elements with a non-blank data-tooltip attr.
            content: {
                attr: 'msb-tooltip' // Tell qTip2 to look inside this attr for its content
            }
        });
    </script>
EOM;
  return $string;
}

function test_apache_solr_ext() {
  $options = array('hostname' => SOLR_SERVER_HOSTNAME, 'port' => SOLR_SERVER_PORT, 'path' => SOLR_SERVER_PATH,);
  $client = new SolrClient($options);
  $query = new SolrQuery('mmd_metadata_status:"active"');
  $query->addField('mmd_metadata_status');
  $query->setFacet(true);
  $query->addFacetField('mmd_keywords_keyword')->addFacetField('mmd_iso_topic_category')->setFacetMinCount(1);
  $updateResponse = $client->query($query);
  $response_array = $updateResponse->getResponse();
  $facet_data = $response_array->facet_counts->facet_fields;
}

function msb_add_children_to_basket($form, $form_state) {
  global $metsis_conf;
  if (array_filter($form_state ['values'] ['table'])) {
    $basket_items = array_filter($form_state ['values'] ['table']);
    $fields = array("id", METADATA_PREFIX . "personnel_email", METADATA_PREFIX . "personnel_organisation", METADATA_PREFIX . "personnel_name", METADATA_PREFIX . "data_access_resource", METADATA_PREFIX . "temporal_extent_start_date", METADATA_PREFIX . "temporal_extent_end_date",);
    $my_items = array();
    foreach ($basket_items as $bi) {
      $my_items[$bi] = msb_get_fields(SOLR_CORE_CHILD, $bi, $fields);
      foreach ($my_items[$bi]['response']['docs'] as $doc) {
        if (!isset($doc[METADATA_PREFIX . 'data_access_resource'])) {
          $message = $bi . " : " . MISSING_METADATA;
          drupal_set_message($message, 'warning');
          unset($my_items[$bi]);
        }
        else {
          $doc[METADATA_PREFIX . 'data_access_resource'] = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
        } $my_items[$bi] = $doc;
      }
    } metsis_basket_insert(trim_mmd_prefix($my_items));
    drupal_set_message(t('The following datasets have been added to your basket:!values', array('!values' => theme('item_list', array('items' => $basket_items)))));
  }
}

function trim_mmd_prefix($object) {
  $rows = array();
  foreach ($object as $o) {
    $row = array('dataset_id' => $o['id'], 'personnel_organisation' => $o[METADATA_PREFIX . 'personnel_organisation'], 'personnel_email' => $o[METADATA_PREFIX . 'personnel_email'], 'personnel_name' => $o[METADATA_PREFIX . 'personnel_name'], 'dataset_dar_http' => $o[METADATA_PREFIX . 'data_access_resource']['HTTP'], 'dataset_dar_ogc_wms' => $o[METADATA_PREFIX . 'data_access_resource']['OGC_WMS'], 'dataset_dar_odata' => $o[METADATA_PREFIX . 'data_access_resource']['ODATA'], 'start_date' => $o[METADATA_PREFIX . 'temporal_extent_start_date'], 'end_date' => $o[METADATA_PREFIX . 'temporal_extent_end_date'],);
    $rows[] = $row;
  } return $rows;
}

function msb_display_children_form($form, &$form_state, $datasetID = '') {
  $children = msb_get_children($datasetID);
  $header = array('dataset_id' => t('Dataset ID'), 'start_date' => t('Start date'), 'end_date' => t('End date'),);
  $rows = array();
  foreach ($children['response']['docs'] as $doc) {
    $dar = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
    $row = array('dataset_id' => l(t($doc['id']), $dar['HTTP']), 'start_date' => msb_get_short_isodate($doc[METADATA_PREFIX . 'temporal_extent_start_date']), 'end_date' => msb_get_short_isodate($doc[METADATA_PREFIX . 'temporal_extent_end_date']),);
    $rows[$doc['id']] = $row;
  } if (BASKET_ELEMENTS_VISIBLE) {
    $form ['table'] = array('#type' => 'tableselect', '#options' => $rows, '#header' => $header, '#js_select' => TRUE,);
  }
  else {
    $form ['table'] = array('#theme' => 'table', '#rows' => $rows, '#header' => $header, '#js_select' => TRUE,);
  } $form['#validate'][] = 'msb_display_children_validate';
  $form['#submit'][] = 'msb_display_children_submit';
  if (BASKET_ELEMENTS_VISIBLE) {
    $form['submit'] = array('#type' => 'submit', '#value' => t('Add to basket'),);
  } $form ['pager'] = array('#theme' => 'pager', '#markup' => theme('pager', array('quantity' => 5)));
  return $form;
}

function msb_display_children_validate() {
  
}

function msb_display_children_submit(&$form, &$form_state) {
  msb_add_children_to_basket($form, $form_state);
}

function msb_markup_opendap_links($opendap_extension_urls, $extension_string) {
  $url = '' . '<div class="botton-wrap">' . '<a class="adc-button adc-sbutton" href="' . $opendap_extension_urls[$extension_string] . '">OPeNDAP[' . $extension_string . ']</a>' . '</div>';
  return $url;
}

function msb_get_feature_type($opendapURL, $featureType) {
  $opendapURL = $opendapURL . ".das";
  $res = drupal_http_request($opendapURL);
}

function mbas_get_children($datasetID) {
  $children = msb_get_children($datasetID);
  $rows = array();
  foreach ($children['response']['docs'] as $doc) {
    $dar = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
    $row = array('dataset_id' => $doc['id'], 'personnel_organisation' => $doc[METADATA_PREFIX . 'personnel_organisation'], 'personnel_email' => $doc[METADATA_PREFIX . 'personnel_email'], 'personnel_name' => $doc[METADATA_PREFIX . 'personnel_name'], 'dataset_dar_http' => $dar['HTTP'], 'dataset_dar_ogc_wms' => $dar['OGC_WMS'], 'dataset_dar_odata' => $dar['ODATA'], 'start_date' => msb_get_short_isodate($doc[METADATA_PREFIX . 'temporal_extent_start_date']), 'end_date' => msb_get_short_isodate($doc[METADATA_PREFIX . 'temporal_extent_end_date']),);
    $rows[] = $row;
  } return $rows;
}

function number_of_children($datasetID) {
  $fields = "numFound";
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . SOLR_CORE_CHILD . '/select', array("q" => METADATA_PREFIX . "related_dataset:$datasetID", "wt" => "json", "fl" => $fields,));
  $children = json_decode($res['body'], true);
  return $children['response']['numFound'];
}

function go_to_start() {
  drupal_goto("metadata_search");
}

function adc_empty_basket() {
  global $user;
  global $metsis_conf;
  $table = 'metsis_basket';
  if (!isset($metsis_conf['basket_endpoint'])) {
    drupal_set_message("No basket view defined. You need to create a basket view and set the variable \$metsis_conf['basket_endpoint'] as administrator first.", 'warning');
  }
  else {
    db_delete($table)->condition('uid', $user->uid)->execute();
  } return;
}

function adc_limit_empty_search($form_state) {
  global $metsis_conf;
  $number_of_empty_fields = 0;
  $number_of_fields_to_check = 0;
  if ($metsis_conf['search_form_fields_empty_check']['chosen_full_text_search'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['chosen_full_text_search']) || is_null($form_state['values']['chosen_full_text_search'])) {
      $number_of_empty_fields += 1;
    }
  } if ($metsis_conf['search_form_fields_empty_check']['chosen_topics_and_variables_a'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['chosen_topics_and_variables_a']) || is_null($form_state['values']['chosen_topics_and_variables_a'])) {
      $number_of_empty_fields += 1;
    }
  } if ($metsis_conf['search_form_fields_empty_check']['cloud_cover_value']['chosen_cloud_cover_value'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['cloud_cover_value']['chosen_cloud_cover_value']) || is_null($form_state['values']['cloud_cover_value']['chosen_cloud_cover_value'])) {
      $number_of_empty_fields += 1;
    }
  } if ($metsis_conf['search_form_fields_empty_check']['finished_after'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['finished_after']) || is_null($form_state['values']['finished_after'])) {
      $number_of_empty_fields += 1;
    }
  } if ($metsis_conf['search_form_fields_empty_check']['finished_before'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['finished_before']) || is_null($form_state['values']['finished_before'])) {
      $number_of_empty_fields += 1;
    }
  } if ($metsis_conf['search_form_fields_empty_check']['bbox_top_left_lon'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['bbox_top_left_lon']) || is_null($form_state['values']['bbox_top_left_lon'])) {
      $number_of_empty_fields += 1;
    }
  } if ($metsis_conf['search_form_fields_empty_check']['bbox_top_left_lat'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['bbox_top_left_lat']) || is_null($form_state['values']['bbox_top_left_lat'])) {
      $number_of_empty_fields += 1;
    }
  } if ($metsis_conf['search_form_fields_empty_check']['bbox_bottom_right_lon'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['bbox_bottom_right_lon']) || is_null($form_state['values']['bbox_bottom_right_lon'])) {
      $number_of_empty_fields += 1;
    }
  } if ($metsis_conf['search_form_fields_empty_check']['bbox_bottom_right_lat'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['bbox_bottom_right_lat']) || is_null($form_state['values']['bbox_bottom_right_lat'])) {
      $number_of_empty_fields += 1;
    }
  } if ($metsis_conf['search_form_fields_empty_check']['chosen_investigator'] === TRUE) {
    $number_of_fields_to_check += 1;
    if (empty($form_state['values']['chosen_investigator']) || is_null($$form_state['values']['chosen_investigator'])) {
      $number_of_empty_fields += 1;
    }
  } if (($metsis_conf['limit_empty_search'] === TRUE) && ($number_of_fields_to_check === $number_of_empty_fields)) {
    return TRUE;
  }
  else {
    return FALSE;
  }
}

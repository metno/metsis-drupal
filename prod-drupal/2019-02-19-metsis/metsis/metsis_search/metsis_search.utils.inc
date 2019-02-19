<?php

function msb_get_children($parent_id) {
    $fields = "id," . METADATA_PREFIX . "personnel_email," . METADATA_PREFIX . "personnel_organisation," . METADATA_PREFIX . "personnel_name," . METADATA_PREFIX . "temporal_extent_start_date," . METADATA_PREFIX . "temporal_extent_end_date," . METADATA_PREFIX . "data_access_resource";
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_CHILD . '/select', array("q" => METADATA_PREFIX . "related_dataset:$parent_id", "rows" => SEARCH_MAXIMUM_ROWS_TO_FETCH, "wt" => "json", "fl" => $fields,));
    $children = json_decode($res['body'], true);
    $children['parent'] = $parent_id;
    return $children;
}

function msb_get_all_children($form_state) {
    $children = array();
    $parents = msb_do_search($form_state);
    foreach ($parents['response']['docs'] as $doc) {
        $children[$doc['id']] = msb_get_children($doc['id']);
    }
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
    } variable_set(METADATA_PREFIX . 'keywords', $phrases);
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

function msb_delete_local_mmd_keywords() {
    variable_del(METADATA_PREFIX . 'keywords');
}

function msb_get_mmd_keywords() {
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => "*:*", "wt" => "json", "fl" => METADATA_PREFIX . "keywords_keyword", "indent" => "true"));
    return json_decode($res['body'], true);
}

function h_adc_has_data_access_type($solr_doc, $data_access_type) {
    if (h_adc_has_data_access_resource($solr_doc) == 1) {
        if (in_array($data_access_type, $solr_doc[METADATA_PREFIX . 'data_access_type'])) {
            return 1;
        }
        else {
            return 0;
        }
    }
}

function msb_date_validate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
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

function msb_get_fields($solr_core, $dataset_id, $fields) {
    $fl = implode(",", $fields);
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . $solr_core . '/select', array("q" => METADATA_PREFIX . "metadata_identifier:" . "\"" . $dataset_id . "\"", "wt" => "json", "fl" => "$fl",));
    return json_decode($res['body'], true);
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

function msb_get_fimex_link($dataset_id, $solr_core) {
    $url = '';
    $url .= '<div class="botton-wrap">';
    $url .= '<a class="adc-button adc-sbutton" href="' . '/metsis_fimex?dataset_id=' . $dataset_id . '&solr_core=' . $solr_core . '" >Transform</a>';
    $url .= '</div>';
    return $url;
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

function adc_longitude_validate() {
    return;
}

function adc_latitude_validate() {
    return;
}

function adc_keywords_to_string($keywords_array) {
    $glue = "<br>";
    return implode($glue, $keywords_array);
}

function adc_goto_basket() {
    global $user;
    global $metsis_conf;
    if (!isset($metsis_conf['basket_endpoint'])) {
        drupal_set_message("No basket view defined. You need to create a basket view and set the variable \$metsis_conf['basket_endpoint'] as administrator first.", 'warning');
    }
    else {
        drupal_goto($metsis_conf['basket_endpoint']);
    } return;
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

<?php

require_once drupal_get_path('module', 'metsis_search') . '/metsis_search.utils.inc';

function q_get_num_res_found($user_query) {
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => $user_query, "start" => 0, "rows" => 1, "wt" => "json",));
    $query_res = json_decode($res['body'], true);
    return $query_res['response']['numFound'];
}

function q_do_search($user_query, $number_of_rows, $start_row) {
    $fields = "id," . METADATA_PREFIX . "personnel_organisation," . METADATA_PREFIX . "abstract," . METADATA_PREFIX . "personnel_name," . METADATA_PREFIX . "title," . METADATA_PREFIX . "temporal_extent_start_date," . METADATA_PREFIX . "temporal_extent_end_date," . METADATA_PREFIX . "personnel_role," . METADATA_PREFIX . "metadata_identifier," . METADATA_PREFIX . "last_metadata_update," . METADATA_PREFIX . "platform_long_name," . METADATA_PREFIX . "cloud_cover_value," . METADATA_PREFIX . "data_center_contact_name," . METADATA_PREFIX . "collection," . METADATA_PREFIX . "geographic_extent_rectangle_east," . METADATA_PREFIX . "geographic_extent_rectangle_south," . METADATA_PREFIX . "geographic_extent_rectangle_west," . METADATA_PREFIX . "geographic_extent_rectangle_north," . METADATA_PREFIX . "data_center_data_center_url," . METADATA_PREFIX . "platform_short_name," . METADATA_PREFIX . "related_information_resource," . METADATA_PREFIX . "project_long_name," . METADATA_PREFIX . "dataset_production_status," . METADATA_PREFIX . "access_constraint," . METADATA_PREFIX . "iso_topic_category," . METADATA_PREFIX . "temporal_extent_start_date," . METADATA_PREFIX . "temporal_extent_end_date," . METADATA_PREFIX . "data_center_data_center_name_long_name," . METADATA_PREFIX . "dataset_language," . METADATA_PREFIX . "data_center_contact_role," . METADATA_PREFIX . "project_short_name," . METADATA_PREFIX . "activity_type," . METADATA_PREFIX . "keywords_keyword," . METADATA_PREFIX . "related_information_type," . METADATA_PREFIX . "operational_status," . METADATA_PREFIX . "instrument_long_name," . METADATA_PREFIX . "data_center_contact_email," . METADATA_PREFIX . "metadata_status," . METADATA_PREFIX . "abstract," . METADATA_PREFIX . "personnel_role," . METADATA_PREFIX . "data_access_description," . METADATA_PREFIX . "data_center_data_center_name_short_name," . METADATA_PREFIX . "data_access_resource," . METADATA_PREFIX . "personnel_email," . "bbox," . METADATA_PREFIX . "data_access_wms_layers_wms_layer," . METADATA_PREFIX . "data_access_type";
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
    foreach ($obj['facet_counts']['facet_fields'][METADATA_PREFIX . 'platform_long_name'] as $pln) {
        if (is_string($pln)) {
            $checkboxes[] = $pln;
        }
    } return $checkboxes;
}

function metsis_qsearch_form_reset($form, &$form_state) {
    global $base_url;
    $url = $base_url;
    $url .= '/';
    $url .= METSIS_QSEARCH_ENDPOINT;
    drupal_goto($url);
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
    } if (defined('COLLECTION')) {
        $qarray[] = COLLECTION;
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
    } if (!empty(msb_get_chosen_platform_long_name($form_state))) {
        $qarray[] = msb_get_chosen_platform_long_name($form_state);
    } if (!empty(msb_get_chosen_cloud_cover_value($form_state))) {
        $qarray[] = msb_get_chosen_cloud_cover_value($form_state);
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
    global $user;
    global $metsis_conf;
    if ($metsis_conf['authentication_required'] === 1) {
        adc_require_login(drupal_get_destination(), $metsis_conf['authentication_default_message']);
    } if (array_filter($form_state ['values'] ['table'])) {
        $basket_items = array_filter($form_state ['values'] ['table']);
        $fields = array("id", "_version_", METADATA_PREFIX . "last_metadata_update", METADATA_PREFIX . "personell_email", METADATA_PREFIX . "platform_long_name", METADATA_PREFIX . "data_center_contact_name", METADATA_PREFIX . "collection", METADATA_PREFIX . "geographic_extent_rectangle_east", METADATA_PREFIX . "geographic_extent_rectangle_south", METADATA_PREFIX . "geographic_extent_rectangle_west", METADATA_PREFIX . "geographic_extent_rectangle_north", METADATA_PREFIX . "data_center_data_center_url", METADATA_PREFIX . "platform_short_name", METADATA_PREFIX . "related_information_resource", METADATA_PREFIX . "project_long_name", METADATA_PREFIX . "data_access_resource", METADATA_PREFIX . "dataset_production_status", METADATA_PREFIX . "access_constraint", METADATA_PREFIX . "iso_topic_category", METADATA_PREFIX . "temporal_extent_start_date", METADATA_PREFIX . "temporal_extent_end_date", METADATA_PREFIX . "data_center_data_center_name_long_name", METADATA_PREFIX . "dataset_language", METADATA_PREFIX . "data_center_contact_role", METADATA_PREFIX . "data_access_type", METADATA_PREFIX . "project_short_name", METADATA_PREFIX . "abstract", METADATA_PREFIX . "activity_type", METADATA_PREFIX . "keywords_keyword", METADATA_PREFIX . "related_information_type", METADATA_PREFIX . "data_access_wms_layers_wms_layer", METADATA_PREFIX . "operational_status", METADATA_PREFIX . "instrument_long_name", METADATA_PREFIX . "personnel_organisation", METADATA_PREFIX . "data_center_contact_email", METADATA_PREFIX . "instrument_short_name", METADATA_PREFIX . "personnel_role", METADATA_PREFIX . "data_access_description", METADATA_PREFIX . "cloud_cover_value", METADATA_PREFIX . "metadata_identifier", METADATA_PREFIX . "data_center_data_center_name_short_name", METADATA_PREFIX . "metadata_status", METADATA_PREFIX . "personnel_name", METADATA_PREFIX . "title", "bbox",);
        $my_items = array();
        foreach ($basket_items as $bi) {
            $my_items[$bi] = msb_get_fields(SOLR_CORE_PARENT, $bi, $fields);
            foreach ($my_items[$bi] as $mibi) {
                if (isset($mibi['error'])) {
                    drupal_set_message($mibi['error']['msg'], 'error');
                }
            } foreach ($my_items[$bi]['response']['docs'] as $doc) {
                if (h_adc_has_data_access_resource($doc) == 0) {
                    
                } if (!isset($doc[METADATA_PREFIX . 'data_access_resource'])) {
                    $message = $bi . " : " . MISSING_DATA_ACCESS_RESOURCE;
                    drupal_set_message($message, 'warning');
                    unset($my_items[$bi]);
                }
                else {
                    $doc[METADATA_PREFIX . 'data_access_resource'] = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
                    $my_items[$bi] = $doc;
                }
            }
        } $basket_query = adc_get_basket_items($user->uid, 'metadata_identifier');
        $basket_query_results = $basket_query->fetchAllAssoc('metadata_identifier');
        $user_basket_content = [];
        foreach ($basket_query_results as $bqr) {
            $user_basket_content[] = $bqr->metadata_identifier;
        } $not_in_basket = array_diff_key($my_items, array_flip($user_basket_content));
        $my_items = $not_in_basket;
        metsis_basket_insert($my_items);
        if (count($my_items) > 0) {
            drupal_set_message(t('The following <b>' . count($my_items) . '</b> datasets have been added to your basket:!values', array('!values' => theme('item_list', array('items' => array_keys($my_items))))));
        }
        else {
            drupal_set_message(t('No new datasets to place in basket.'));
        }
    }
}

function adc_min_select_validate($form, &$form_state) {
    if (!array_filter($form_state ['values'] ['table'])) {
        form_set_error('', t('You must choose atleast one item!'));
    }
}

function metsis_map_wms() {
    $query = drupal_get_query_parameters();
    if (count($query) > 0) {
        $datasets = explode(",", $query['dataset']);
        $externalURL = $datasets;
    } $page = get_metsis_map_wms_markup($externalURL);
    print $page;
}

function adc_get_geographical_search_map() {
    global $metsis_conf;
    $search_map_center_lat = $metsis_conf['search_map_center_lat'];
    $search_map_center_lon = $metsis_conf['search_map_center_lon'];
    $search_map_init_zoom = $metsis_conf['search_map_init_zoom'];
    $string = <<<EOM
        <div class="map container">
            <div id="map"></div>                   
        </div>
        <script type="text/javascript">
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
        </script> 
EOM;
    return $string;
}

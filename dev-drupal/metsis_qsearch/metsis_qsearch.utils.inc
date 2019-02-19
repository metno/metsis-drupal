<?php

require_once drupal_get_path('module', 'metsis_search') . '/metsis_search.utils.inc';

function q_get_num_res_found($user_query) {
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array(
      "q" => $user_query,
      "start" => 0,
      "rows" => 1,
      "wt" => "json",
        )
    );
    $query_res = json_decode($res['body'], true);
    return $query_res['response']['numFound'];
}

/*
 * msb_do_search{
 */

function q_do_search($user_query, $number_of_rows, $start_row) {
    $fields = "id,"
        . METADATA_PREFIX . "personnel_organisation,"
        . METADATA_PREFIX . "abstract,"
        . METADATA_PREFIX . "personnel_name,"
        . METADATA_PREFIX . "title,"
        . METADATA_PREFIX . "temporal_extent_start_date,"
        . METADATA_PREFIX . "temporal_extent_end_date,"
        . METADATA_PREFIX . "personnel_role,"
        . METADATA_PREFIX . "metadata_identifier,"
        . METADATA_PREFIX . "last_metadata_update,"
        . METADATA_PREFIX . "platform_long_name,"
        . METADATA_PREFIX . "cloud_cover_value,"
        . METADATA_PREFIX . "data_center_contact_name,"
        . METADATA_PREFIX . "collection,"
        . METADATA_PREFIX . "geographic_extent_rectangle_east,"
        . METADATA_PREFIX . "geographic_extent_rectangle_south,"
        . METADATA_PREFIX . "geographic_extent_rectangle_west,"
        . METADATA_PREFIX . "geographic_extent_rectangle_north,"
        . METADATA_PREFIX . "data_center_data_center_url,"
        . METADATA_PREFIX . "platform_short_name,"
        . METADATA_PREFIX . "related_information_resource,"
        . METADATA_PREFIX . "project_long_name,"
        . METADATA_PREFIX . "dataset_production_status,"
        . METADATA_PREFIX . "access_constraint,"
        . METADATA_PREFIX . "iso_topic_category,"
        . METADATA_PREFIX . "temporal_extent_start_date,"
        . METADATA_PREFIX . "temporal_extent_end_date,"
        . METADATA_PREFIX . "data_center_data_center_name_long_name,"
        . METADATA_PREFIX . "dataset_language,"
        . METADATA_PREFIX . "data_center_contact_role,"
        . METADATA_PREFIX . "project_short_name,"
        . METADATA_PREFIX . "activity_type,"
        . METADATA_PREFIX . "keywords_keyword,"
        . METADATA_PREFIX . "related_information_type,"
        . METADATA_PREFIX . "operational_status,"
        . METADATA_PREFIX . "instrument_long_name,"
        . METADATA_PREFIX . "data_center_contact_email,"
        . METADATA_PREFIX . "metadata_status,"
        . METADATA_PREFIX . "abstract,"
        . METADATA_PREFIX . "personnel_role,"
        . METADATA_PREFIX . "data_access_description,"
        . METADATA_PREFIX . "data_center_data_center_name_short_name,"
        . METADATA_PREFIX . "data_access_resource,"
        . METADATA_PREFIX . "personnel_email,"
        . "bbox,"
        . METADATA_PREFIX . "data_access_wms_layers_wms_layer,"
        . METADATA_PREFIX . "data_access_type,"
        . METADATA_PREFIX . "related_dataset";

    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array(
      "q" => $user_query,
      //TODO: sort should be configurable on a per site basis
      //      needs entries in metsis.settings.php and 
      //      metsis_qsearch.constants.inc
      "sort" => "mmd_temporal_extent_start_date desc",
      "rows" => $number_of_rows,
      "start" => $start_row,
      "wt" => "json",
      "fl" => $fields,
        )
    );
    $query_res = json_decode($res['body'], true);
    /**
     * test{
     */
//$myjstring='{
//  "name": "coads_climatology.nc",
//  "attributes": [
//    {
//      "name": "NC_GLOBAL",
//      "attributes": [
//        {"name": "history", "value": ["FERRET V4.30 (debug/no GUI) 15-Aug-96"]}
//      ]
//    },
//    {
//      "name": "DODS_EXTRA",
//      "attributes": [
//        {"name": "Unlimited_Dimension", "value": ["TIME"]}
//      ]
//    }
//  ],
//  "leaves": [
//    {
//      "name": "COADSX",
//      "type": "f",
//      "attributes": [
//        {"name": "units", "value": ["degrees_east"]},
//        {"name": "modulo", "value": [" "]},
//        {"name": "point_spacing", "value": ["even"]}
//      ],
//      "shape": [180]
//    }
//  ],
//  "nodes": []
//}';
//$myjdecoded= json_decode($myjstring,true);
//sdpm($myjdecoded);
    /**
     * test}
     */
    $counter = 0;
    foreach ($query_res['response']['docs'] as $doc) {
        if (!empty($doc[METADATA_PREFIX . 'data_access_resource'])) {
            $query_res['response']['docs'][$counter][METADATA_PREFIX . 'data_access']['type'] = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
        }
        $counter = $counter + 1;
    }
    /**
     * test{
     */
//    foreach ($query_res['response']['docs'] as $doc) {
//        if (isset($doc['mmd_related_dataset'])) {
//            if (count($doc['mmd_related_dataset']) > 0) {
//                sdpm($doc[METADATA_PREFIX . 'metadata_identifier'] . " has child datasets and cannot be added to basket");
//            }
//        }
//    }
    /**
     * test}
     */
    return $query_res;
}

function q_get_platform_long_name() {
    //$obj = metsis_get_parent_facet_field(METADATA_PREFIX . 'platform_long_name');
    $obj = metsis_qget_parent_facet_field(METADATA_PREFIX . 'platform_long_name');
    $checkboxes = [];
    while ($current = current($obj['facet_counts']['facet_fields'][METADATA_PREFIX . 'platform_long_name'])) {
        $cstring = (string) $current . '  (' . (string) next($obj['facet_counts']['facet_fields'][METADATA_PREFIX . 'platform_long_name']) . ')';
        $checkboxes[strtolower(str_replace('-', '_', $current))] = $cstring;
        $current = next($obj['facet_counts']['facet_fields'][METADATA_PREFIX . 'platform_long_name']);
    }

//    foreach ($obj['facet_counts']['facet_fields'][METADATA_PREFIX . 'platform_long_name'] as $pln) {
//        if (is_string($pln)) {
//            $checkboxes[] = $pln;
//        }
//    }
    return $checkboxes;
}

function metsis_qsearch_form_reset($form, &$form_state) {
    global $base_url;
    //$form_state['rebuild'] = FALSE;
    $url = $base_url;
    $url .= '/';
    $url .= METSIS_QSEARCH_ENDPOINT;
    drupal_goto($url);
}

/*
 * validate text searches{
 */

function msb_text_qsearch_validate($element, $form_state) {
//  TODO-03
//  need to allow European alphabet (eg. UTF-8), but stop dangerous charachters.
//    if (!preg_match('/^[0-9a-zA-Z\s\*\"\'\(\)>\/\_\-]*$/', $element['#value'])) {
//        form_error($element, t('Allowed characters are: 0-9, A-Z, a-z, asterisk "*", ">", "/", "_", "-", pairs of single or double quotes, pairs of parentheses, spaces and an empty field.'));
//    }
}

/*
 * validate text searches}
 */


/*
 * get final SOLR query string{
 */

function msb_get_final_solr_qq($form_state) {
    $qarray = array();
    $q = '*:*';
    //sdpm($form_state['values']);
    /**
     * mmd_metadata_status = "active" is assumed for all queries in the Drupal portal.
     */
    if (defined('SEARCH_DEFAULT_METADATA_STATUS')) {
        $qarray[] = (METADATA_PREFIX . 'metadata_status:"' . SEARCH_DEFAULT_METADATA_STATUS . '"');
    }
    else {
        $qarray[] = (METADATA_PREFIX . 'metadata_status:"active"');
    }
    if (defined('COLLECTIONS')) {
        $qarray[] = (METADATA_PREFIX . 'collection:' . COLLECTIONS . '');
    }
    if (!empty(adc_get_full_text_search($form_state))) {
        $qarray[] = adc_get_full_text_search($form_state);
    }

    if (!empty(adc_get_investigator($form_state))) {
        $qarray[] = adc_get_investigator($form_state);
    }
    if (!empty(msb_get_chosen_topics_and_variables($form_state))) {
        $qarray[] = msb_get_chosen_topics_and_variables($form_state);
    }
    if (!empty(msb_get_chosen_operational_status($form_state))) {
        $qarray[] = msb_get_chosen_operational_status($form_state);
    }
    if (!empty(msb_get_chosen_institutions($form_state))) {
        $qarray[] = msb_get_chosen_institutions($form_state);
    }
    if (!empty(msb_get_chosen_collections($form_state))) {
        $qarray[] = msb_get_chosen_collections($form_state);
    }
    //nsb{
    /**
     * todo 00
     * add getters for all the new fields 
     * add logic to the getters
     */
    if (!empty(msb_get_chosen_platform_long_name($form_state))) {
        $qarray[] = msb_get_chosen_platform_long_name($form_state);
    }
//    if (!empty(adc_get_chosen_acquisition_modes($form_state))) {
//        $qarray[] = adc_get_chosen_acquisition_modes($form_state);
//    }
//    if (!empty(adc_get_chosen_product_types($form_state))) {
//        $qarray[] = adc_get_chosen_product_types($form_state);
//    }
//    if (!empty(adc_get_chosen_polarisation_modes($form_state))) {
//
//        $qarray[] = adc_get_chosen_polarisation_modes($form_state);
//    }
//    /**
//     * get chosen cloud cover value ONLY if any of sentinel 2 or sentinel 3 is chosen
//     */
//    if (!empty(msb_get_chosen_cloud_cover_value($form_state))) {
//        $qarray[] = msb_get_chosen_cloud_cover_value($form_state);
//    }
    //nsb}
    if (!empty(adc_get_chosen_dates($form_state))) {
        $qarray[] = adc_get_chosen_dates($form_state);
    }
    if (!empty(adc_get_geographical_search_q($form_state))) {
        $qarray[] = adc_get_geographical_search_q($form_state);
    }
    if (!empty($qarray)) {
        $q = implode(" AND ", $qarray);
    }
    //if (DEBUG) {
    //sdpm("SOLR query string: ");
    //sdpm($q);
    // }
    return $q;
}

/*
 * get final SOLR query string}
 */
//http://157.249.176.182:8080/solr/dev-l1/select?q=*%3A*&sort=mmd_temporal_extent_end_date+desc&fl=mmd_temporal_extent_end_date&wt=json&indent=true

/**
 * metsis_get_parent_facet_field{
 * @param type $mmd_metadata_field
 * @return type
 */
function metsis_qget_parent_facet_field($mmd_metadata_field) {
    $facet_con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $facet_qres = $facet_con->get('/solr/' . SOLR_CORE_PARENT . '/select', array(
      "q" => "*:*",
      "rows" => 1,
      "facet" => "true",
      "facet.field" => $mmd_metadata_field,
      "wt" => "json",
        )
    );

    return json_decode($facet_qres['body'], true);
}

/**
 * metsis_get_parent_facet_field}
 */

/**
 * adc_add_to_basket{
 * 
 */
function adc_add_to_basket(&$form, &$form_state) {
    global $user;
    global $metsis_conf;
    //todo refactor
    //if ($metsis_conf['metsis_basket_authentication_required']['boolean'] === TRUE) {
    if ($metsis_conf['authentication_required'] === 1) {
        adc_require_login(drupal_get_destination(), $metsis_conf['authentication_default_message']);
    }

    if (array_filter($form_state ['values'] ['table'])) {
        $basket_items = array_filter($form_state ['values'] ['table']);

        $not_basketable = adc_has_related_dataset(SOLR_CORE_PARENT, array_keys($basket_items));
        if (count($not_basketable) > 0) {
            drupal_set_message(t('The following <b>' . count($not_basketable) . '</b> are aggregate datasets. To add them to basket choose from their "Child data" !values', array(
              '!values' => theme('item_list', array(
                'items' => array_values($not_basketable)
              ))
            )));
            $basket_items = array_diff($basket_items, array_keys($not_basketable));
        }

        $fields = array(
          "id",
          "_version_",
          //
          METADATA_PREFIX . "last_metadata_update",
          METADATA_PREFIX . "personell_email",
          METADATA_PREFIX . "platform_long_name",
          METADATA_PREFIX . "data_center_contact_name",
          METADATA_PREFIX . "collection",
          METADATA_PREFIX . "geographic_extent_rectangle_east",
          METADATA_PREFIX . "geographic_extent_rectangle_south",
          METADATA_PREFIX . "geographic_extent_rectangle_west",
          METADATA_PREFIX . "geographic_extent_rectangle_north",
          METADATA_PREFIX . "data_center_data_center_url",
          METADATA_PREFIX . "platform_short_name",
          METADATA_PREFIX . "related_information_resource",
          METADATA_PREFIX . "project_long_name",
          //data access resource is parsed elsewhere.
          METADATA_PREFIX . "data_access_resource",
//          METADATA_PREFIX . "data_access_resource_HTTP",
//          METADATA_PREFIX . "data_access_resource_OPeNDAP",
//          METADATA_PREFIX . "data_access_resource_OGC_WMS",
//          METADATA_PREFIX . "data_access_resource_HTTPserver",
          METADATA_PREFIX . "dataset_production_status",
          METADATA_PREFIX . "access_constraint",
          METADATA_PREFIX . "iso_topic_category",
          METADATA_PREFIX . "temporal_extent_start_date",
          METADATA_PREFIX . "temporal_extent_end_date",
          METADATA_PREFIX . "data_center_data_center_name_long_name",
          METADATA_PREFIX . "dataset_language",
          METADATA_PREFIX . "data_center_contact_role",
          METADATA_PREFIX . "data_access_type",
          METADATA_PREFIX . "project_short_name",
          METADATA_PREFIX . "abstract",
          METADATA_PREFIX . "activity_type",
          METADATA_PREFIX . "keywords_keyword",
          METADATA_PREFIX . "related_information_type",
          METADATA_PREFIX . "data_access_wms_layers_wms_layer",
          METADATA_PREFIX . "operational_status",
          METADATA_PREFIX . "instrument_long_name",
          METADATA_PREFIX . "personnel_organisation",
          METADATA_PREFIX . "data_center_contact_email",
          METADATA_PREFIX . "instrument_short_name",
          METADATA_PREFIX . "personnel_role",
          METADATA_PREFIX . "data_access_description",
          METADATA_PREFIX . "cloud_cover_value",
          METADATA_PREFIX . "metadata_identifier",
          METADATA_PREFIX . "data_center_data_center_name_short_name",
          METADATA_PREFIX . "metadata_status",
          METADATA_PREFIX . "personnel_name",
          METADATA_PREFIX . "title",
          "bbox",
        );
        $my_items = array();

        foreach ($basket_items as $bi) {
            $my_items[$bi] = msb_get_fields(SOLR_CORE_PARENT, $bi, $fields);

            foreach ($my_items[$bi] as $mibi) {
                if (isset($mibi['error'])) {
                    drupal_set_message($mibi['error']['msg'], 'error');
                }
            }
            foreach ($my_items[$bi]['response']['docs'] as $doc) {

                if (h_adc_has_data_access_resource($doc) == 0) {
                    
                }
                if (!isset($doc[METADATA_PREFIX . 'data_access_resource'])) {
                    $message = $bi . " : " . MISSING_DATA_ACCESS_RESOURCE;
                    drupal_set_message($message, 'warning');
                    unset($my_items[$bi]);
                }
                else {
                    $doc[METADATA_PREFIX . 'data_access_resource'] = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
                    $my_items[$bi] = $doc;
                }
            }
        }
        //reroute to mbas and remove msb_basket_table_insert
        //
        //todo5
        //refactor
        //todo0 basket items are added multiple times!! This is wrong.
        $basket_query = adc_get_basket_items($user->uid, 'metadata_identifier');
        $basket_query_results = $basket_query->fetchAllAssoc('metadata_identifier');
        $user_basket_content = [];
        foreach ($basket_query_results as $bqr) {
            $user_basket_content[] = $bqr->metadata_identifier;
        }

        $not_in_basket = array_diff_key($my_items, array_flip($user_basket_content));
        $my_items = $not_in_basket;
        metsis_basket_insert($my_items);
        if (count($my_items) > 0) {
            drupal_set_message(t('The following <b>' . count($my_items) . '</b> datasets have been added to your basket:!values', array(
              '!values' => theme('item_list', array(
                'items' => array_keys($my_items)
                  //'items' => $basket_items
              ))
            )));
        }
        else {
            drupal_set_message(t('No new datasets to place in basket.'));
        }
    }
}

/*
 * adc_add_to_basket}
 */

/**
 * adc_min_select_validate{
 * @param type $form
 * @param type $form_state
 */
function adc_min_select_validate($form, &$form_state) {
    if (!array_filter($form_state ['values'] ['table'])) {
        form_set_error('', t('You must choose atleast one item!'));
    }
}

/**
 * adc_min_select_validate}
 */
/*
 * wms{
 * TODO: parameterization
 */

function metsis_map_wms() {

    $query = drupal_get_query_parameters();
    //print_r($query);

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
    //$page = get_metsis_map_wms_markup($externalURL);
    //$page = get_metsis_map_wms_markup($solr_core,$externalURL);
    print $page;
}

/*
 * get geographical search map{
 */

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

/*
 * get geographical search map}
 */
/*
 * adc_get_leveltwo_links{
 * the link must be to the service endpoing like it is for timeseries
 */

function adc_get_leveltwo_links($dataset_id) {
    $number_of_children = msb_count_children($dataset_id);
    $this_url = url(current_path(), array('absolute' => TRUE, 'query' => drupal_get_query_parameters()));
    $elements_url = "/" . ELEMENTS_ENDPOINT . "?metadata_identifier=" . $dataset_id;
//    $elements_url .= "&calling_results_page=" . $this_url;
    /**
     * test{
     * pagination
     */
    $elements_url .= "&calling_results_page=" . $this_url;
    $elements_url .= "&page=1";
    /**
     * pagination
     * test}
     */
//    global $base_url;   
//    if ($number_of_children > 0) {
//        $url = ''
//            . '<div class="botton-wrap">'
//            . '<a class="adc-button adc-sbutton" href="' . $base_url . '/' . 'metsis/display/children/' . $dataset_id . '" >' . LEVELTWO_BUTTON_TEXT . '..[' . $number_of_children . ']</a>'
//            . '</div>';
//        return $url;
//
//        }
    if ($number_of_children > 0) {
        $url = ''
            . '<div class="botton-wrap">'
            . '<a class="adc-button adc-sbutton" href="' . $elements_url . '" >' . ELEMENTS_BUTTON_TEXT . '..[' . $number_of_children . ']</a>'
            . '</div>';
        return $url;
    }
}

/*
 * adc_get_leveltwo_links}
 */
/*
 * msb_count_children{
 * @param type $datasetID
 * @return integer
 * needs to be changed to just count the number of elements in the 
 * mmd_related_dataset... array
 * http://157.249.176.182:8080/solr/test-l1/select?q=mmd_metadata_identifier%3A%224307310f-6e94-582a-9d6e-60ae5907a78a%22&rows=1&fl=mmd_related_dataset&wt=json&indent=true
 */

function msb_count_children($datasetID) {
    $datasetID = '("' . $datasetID . '")';
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);

    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array(
      "q" => METADATA_PREFIX . "metadata_identifier:$datasetID",
      "fl" => METADATA_PREFIX . "related_dataset",
      "rows" => 1,
      "wt" => "json",
      "indent" => "true")
    );
    /**
     * test{
     */
//    $solr_url="http://157.249.176.182:8080/solr/test-l1/select?q=mmd_metadata_identifier%3A%224307310f-6e94-582a-9d6e-60ae5907a78a%22&rows=1&fl=mmd_related_dataset&wt=json&indent=true";
//    $jsonData = json_decode(file_get_contents($solr_url));
//    sdpm($jsonData);
    /**
     * test}
     */
    $body = json_decode($res['body'], true);

    if (isset($body['response']['docs'][0]['mmd_related_dataset'])) {
        return(count($body['response']['docs'][0]['mmd_related_dataset']));
    }
    return;
}

/*
 * msb_count_children}
 */

/*
 * get_data_access_http{
 */

function get_data_access_markup($dataset_id, $data_access) {
    $url = ''
        . '<div class="botton-wrap ext_data_source">'
//        . '<a class="adc-button adc-sbutton" href="' . $data_access . '" target="_blank">' . $dataset_id . '</a>'
        . '<a class="adc-button adc-sbutton ext_data_source" href="' . $data_access . '">' . $dataset_id . '</a>'
        . '</div>';
    return $url;
}

/*
 * get_data_access_http}
 */

/**
 * metsis_qsearch_form_alter{
 */
function metsis_qsearch_form_alter(&$form, &$form_state, $form_id) {
    if ($form_id == 'q_results_tabular_form') {
        // does nothing for now. It turns out not to be possible to disable
        // a subset of the checkboxes in tableselect
        //$form['checkbox']['#disabled'] = TRUE; //disables all options
        // $form['checkboxes_element'][abc]['#disabled'] = TRUE; //disables option, called abc
    }
}

/**
 * metsis_qsearch_form_alter}
 */
/*
 * get chosen platform_long_name{
 * very specifig to NBS!
 */
function msb_get_chosen_platform_long_name($form_state) {
    $q = "";
    if (PLATFORM_LONG_NAME_VISIBLE) {
        $sqa = [];
        //Sentinel-1A
        if (isset($form_state['values']['platform_long_name']['sentinel_1a'])) {
            if (!empty(array_filter($form_state['values']['platform_long_name']['sentinel_1a']['chosen_platform_long_name']))) {
                $s1aqa = [];
                $s1aqa[] = "mmd_platform_long_name:(\"Sentinel-1A\")";
                $s1aqa[] = adc_get_chosen_instrument_modes($form_state, 'sentinel_1a');
                $s1aqa[] = adc_get_chosen_instrument_polarisations($form_state, 'sentinel_1a');
                $sqa[] = "(" . implode(" AND ", array_filter($s1aqa)) . ")";
            }
        }
        //Sentinel-1B
        if (isset($form_state['values']['platform_long_name']['sentinel_1b'])) {
            if (!empty(array_filter($form_state['values']['platform_long_name']['sentinel_1b']['chosen_platform_long_name']))) {
                $s1bqa = [];
                $s1bqa[] = "mmd_platform_long_name:(\"Sentinel-1B\")";
                $s1bqa[] = adc_get_chosen_instrument_modes($form_state, 'sentinel_1b');
                $s1bqa[] = adc_get_chosen_instrument_polarisations($form_state, 'sentinel_1b');
                $sqa[] = "(" . implode(" AND ", array_filter($s1bqa)) . ")";
            }
        }
        //Sentinel-2A
        if (isset($form_state['values']['platform_long_name']['sentinel_2a'])) {
            if (!empty(array_filter($form_state['values']['platform_long_name']['sentinel_2a']['chosen_platform_long_name']))) {
                $s2aqa = [];
                $s2aqa[] = "mmd_platform_long_name:(\"Sentinel-2A\")";
                $s2aqa[] = adc_get_chosen_product_types($form_state, 'sentinel_2a');
                $s2aqa[] = msb_get_chosen_cloud_cover_value($form_state, 'sentinel_2a');
                $sqa[] = "(" . implode(" AND ", array_filter($s2aqa)) . ")";
            }
        }
        //Sentinel-2B
        if (isset($form_state['values']['platform_long_name']['sentinel_2b'])) {
            if (!empty(array_filter($form_state['values']['platform_long_name']['sentinel_2b']['chosen_platform_long_name']))) {
                $s2bqa = [];
                $s2bqa[] = "mmd_platform_long_name:(\"Sentinel-2B\")";
                $s2bqa[] = adc_get_chosen_product_types($form_state, 'sentinel_2b');
                $s2bqa[] = msb_get_chosen_cloud_cover_value($form_state, 'sentinel_2b');
                $sqa[] = "(" . implode(" AND ", array_filter($s2bqa)) . ")";
            }
        }
        if (implode(" OR ", array_filter($sqa)) !== '') {
            $q = "(" . implode(" OR ", array_filter($sqa)) . ")";
        }
    }
    return $q;
}

/*
 * get chosen platform_long_name}
 */
/*
 * get chosen instrument_modes{
 */

function adc_get_chosen_instrument_modes($form_state, $platform) {
    $q = "";
    $chosen_instrument_modes = array_filter($form_state['values']['platform_long_name'][$platform]['instrument_modes']['chosen_instrument_modes']);
    if (!empty($chosen_instrument_modes)) {
        $q = "mmd_instrument_mode:(\"" . implode("\" OR \"", $chosen_instrument_modes) . "\")";
    }
    return $q;
}

/*
 * get chosen instrument_modes}
 */
/*
 * get chosen product types{
 */

function adc_get_chosen_product_types($form_state, $platform) {
    $q = "";
    $chosen_product_types = array_filter($form_state['values']['platform_long_name'][$platform]['product_types']['chosen_product_types']);
    if (!empty($chosen_product_types)) {
        $q = "mmd_product_type:(\"" . implode("\" OR \"", $chosen_product_types) . "\")";
    }
    return $q;
}

/*
 * get chosen product types}
 */
/*
 * get chosen instrument_polarisations{
 */

function adc_get_chosen_instrument_polarisations($form_state, $platform) {
    $q = "";
    if (INSTRUMENT_POLARISATION_VISIBLE) {
        $chosen_instrument_polarisations = array_filter($form_state['values']['platform_long_name'][$platform]['instrument_polarisations']['chosen_instrument_polarisations']);
        if (!empty($chosen_instrument_polarisations)) {
            $q = "mmd_instrument_polarisation:(\"" . implode("\" OR \"", $chosen_instrument_polarisations) . "\")";
        }
    }
    return $q;
}

/*
 * get chosen instrument_polarisations}
 */

/*
 * get cloud_cover_value{
 * NBS specific
 */

function msb_get_chosen_cloud_cover_value($form_state, $platform) {
    global $metsis_conf;
    $q = "";
    if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
        //KEEP THE COMMENTS
        //for now we are using single value radios
        //we may end up with checkboxes in which case we need array_filter etc.
        //$chosen_cloud_cover_value = array_filter($form_state['values']['cloud_cover_value']['chosen_cloud_cover_value']);
        //$chosen_cloud_cover_value = $form_state['values']['cloud_cover_value']['chosen_cloud_cover_value'];

        /**
         * test{
         */
        //sdpm("cloud_cover_value = " . $form_state['values']['cloud_cover_value']['chosen_cloud_cover_value']);
        //sdpm($form_state);
        /**
         * test}
         */
        //if (!empty($form_state['input']['platform_long_name']['sentinel_2b']['cloud_cover_value']['chosen_cloud_cover_value'])) {
        $chosen_cloud_cover_value = $form_state['values']['platform_long_name'][$platform]['cloud_cover_value']['chosen_cloud_cover_value'];
        if (!empty($chosen_cloud_cover_value)) {
            if ($chosen_cloud_cover_value == ">90%") {
                $q = "mmd_cloud_cover_value:[90 TO *]";
                /*
                 * todo0
                 * test{
                 * currently these are indexed as strings arrays which is wrong.
                 * hacking it with regex is not the way to go but it can be done.
                 * Simplictic regex won't work though. The line below will return
                 * 9.123 as well as 91.123 and 100*  
                 */
                //$q = "mmd_cloud_cover_value:(9*.* OR 100*)";
                /*
                 * test}
                 */
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
            //todo01
            //get the string and strip the inequality and % symbols
            //construct [* TO value] unless we are at >90% in 
            //which case [90 TO *]
            //$q = "mmd_cloud_cover_value:(\"" . implode("\" OR \"", $chosen_cloud_cover_value) . "\")";
            //$q = "mmd_cloud_cover_value:" . $chosen_cloud_cover_value;
        }
    }
    return $q;
}

/*
 * get cloud_cover_value}
 */

<?php

/**
 * get children of a given dataset{
 */
function msb_get_children($parent_id) {

    $fields = "id,"
        . METADATA_PREFIX . "personnel_email,"
        . METADATA_PREFIX . "personnel_organisation,"
        . METADATA_PREFIX . "personnel_name,"
        . METADATA_PREFIX . "temporal_extent_start_date,"
        . METADATA_PREFIX . "temporal_extent_end_date,"
        . METADATA_PREFIX . "data_access_resource";
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_CHILD . '/select', array(
      "q" => METADATA_PREFIX . "related_dataset:$parent_id",
      "rows" => SEARCH_MAXIMUM_ROWS_TO_FETCH,
      "wt" => "json",
      "fl" => $fields,
        )
    );
    $children = json_decode($res['body'], true);
    $children['parent'] = $parent_id;
    return $children;
}

/*
 * get children dataset}
 */

/**
 * ALL children (all selected datasets){
 */
function msb_get_all_children($form_state) {
    $children = array();
    $parents = msb_do_search($form_state);
    foreach ($parents['response']['docs'] as $doc) {
        $children[$doc['id']] = msb_get_children($doc['id']);
    }
}

/*
 * get ALL children (all selected datasets)}
 */

/*
 * update_local_mmd_keywords{
 */

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
    }
    variable_set(METADATA_PREFIX . 'keywords', $phrases);
}

/*
 * update_local_mmd_keywords}
 */
/*
 * autocomplete must be "liberal". We do not restrict the match to "starting with".
 */

function msb_mmd_keywords_autocomplete($string = ' ') {
    $matches = array();
    $mmd_keywords = variable_get(METADATA_PREFIX . 'keywords');
    foreach ($mmd_keywords as $kw) {
        if (stristr($kw, $string)) {
            $matches[$kw] = $kw;
        }
    }
    drupal_json_output($matches);
}

function msb_delete_local_mmd_keywords() {
    variable_del(METADATA_PREFIX . 'keywords');
}

/*
 * get keywords{
 */

function msb_get_mmd_keywords() {
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => "*:*",
      //"rows" => SEARCH_MAXIMUM_ROWS_TO_FETCH,
      "wt" => "json",
      "fl" => METADATA_PREFIX . "keywords_keyword",
      "indent" => "true")
    );
    return json_decode($res['body'], true);
}

/*
 * get keywords}
 */
//
///*
// * get_metadata{
// * todo 8
// * refactor and use adc_parse_solr_mmd_type_one() etc.
// */
//
//function adc_get_metadata() {
//    global $metsis_conf;
//    //$datasetID = filter_input(INPUT_GET, "datasetID");
//    $metadata_sort_order = $metsis_conf['metadata_sort_order'];
//    $page_inputs = drupal_get_query_parameters();
//    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
//    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array(
//      // "q" => "id:$datasetID",
////      "q" => "id:" . $page_inputs['datasetID'],
//      "q" => METADATA_PREFIX . "metadata_identifier:" . '"' . $page_inputs['datasetID'] . '"',
//      "wt" => "json",
//      "indent" => "true")
//    );
//    $body = json_decode($res['body'], true);
//    if (array_filter($body['response']['docs'][0])) {
//        //get list of element from $global (set in metsis.settings.php)
//        //$metadata = array_filter($body['response']['docs'][0]);
//        $metadata = array_filter($body['response']['docs'][0]);
//        //the value indexed in SOLR should be used without the need for change
//        //this is costly.
//        if (isset($metadata[METADATA_PREFIX . 'cloud_cover_value'])) {
//            //todo00 refactor $metsis_conf to metsis_lib.constants.inc
//            $metadata[METADATA_PREFIX . 'cloud_cover_value'] = number_format($metadata[METADATA_PREFIX . 'cloud_cover_value'], $metsis_conf['results_number_decimal_display_format']);
//        }
//        $zmetadata = array_intersect_key($metadata, array_flip($metsis_conf['metadata_visible']));
//        $metadata = $zmetadata;
//
//        $header = array('Metadata key', 'Metadata value');
//
//        //split the array into top and bottom
//        //top is sorted according to $metsis_conf['metadata_sort_order']
//        //bottom is sorted alphabetically
//        $top_array = array();
//        $bottom_array = array();
//        foreach ($metadata as $k => $v) {
//            if (in_array($k, $metadata_sort_order)) {
//                $top_array[$k] = $v;
//            }
//            else {
//                $bottom_array[$k] = $v;
//            }
//        }
//        $sorted_top_array = array();
//        foreach (array_values($metadata_sort_order) as $key) {
//            //skip MMD elements that are in $metsis_conf['metadata_sort_order']
//            //but not in the metadata
//            //TODO must distintuish between mandatory and optional elements
//            //since the absence of the latter should not cause error
//            if (key_exists($key, $top_array)) {
//                $sorted_top_array[$key] = $top_array[$key];
//            }
//        }
//        ksort($bottom_array);
//        $top_rows = h_adc_get_metadata($sorted_top_array);
//        $bottom_rows = h_adc_get_metadata($bottom_array);
//        $rows = array_merge($top_rows, $bottom_rows);
//
//        //$final_themed_content = 
//
//        return theme('table', array(
//          'header' => $header,
//          'rows' => $rows,
//          'attributes' => array(
//            'class' => array('ext_data_souce'),
//          ),
//            )
//        );
//    }
//}
//
////h_adc_get_metadata{
////"h"_elper function for adc_get_metadata
////This ties the code tightly with the structure of the data elements in SOLR.
////It is hard to avoid given the requirements of the project and the way 
////the data is indexed in SOLR. 
////A much cleaner and more generic approach would be possible if the data 
////could be indexed in standard structures, such as single or multidimensional
////(associative) arrays in SOLR.
//
//function h_adc_get_metadata($metadata) {
//    $rows = array();
//    foreach ($metadata as $key => $value) {
//        if ($key == METADATA_PREFIX . "data_access_resource") {
//            foreach ($value as $k => $v) {
//                $la = explode(":", $v, 2);
//                $ma = explode(",", $la[1]);
//                $na = explode(":", $ma[1]);
//
//                $protocol = str_replace('"', '', $la[0]);
//                switch ($protocol) {
//                    case "OPeNDAP";
//                        $url_ext = ".html";
//                        break;
//                    case "OGC WMS";
//                        $url_ext = "?SERVICE=WMS&REQUEST=GetCapabilities";
//                        break;
//                    default:
//                        $url_ext = "";
//                        break;
//                }
//                $value[$k] = $protocol . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
//                //$value[$k] = $na[1] . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
//            }
//        }
//        if ($key == METADATA_PREFIX . "related_information_resource") {
//            foreach ($value as $k => $v) {
//                $la = explode(":", $v, 2);
//                $ma = explode(",", $la[1]);
//                $na = explode(":", $ma[1]);
//
//                $protocol = str_replace('"', '', $la[0]);
//                switch ($protocol) {
//                    default:
//                        $url_ext = "";
//                        break;
//                }
//                $value[$k] = $protocol . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
//                //$value[$k] = $na[1] . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
//            }
//        }
//        if ($key == METADATA_PREFIX . "data_center_data_center_url") {
//            $value = '<a class="' . $key . '" href=' . $value . '>' . $value . '</a>';
//        }
//        if ($key == METADATA_PREFIX . "data_center_contact_email") {
//
//            $value = '<a class="' . $key . '" href=mailto:' . $value . '>' . $value . '</a>';
//        }
//        if ($key == METADATA_PREFIX . "personnel_email") {
//            //todo 5 
//            //this field can have multiple values.
//            //need to link all emails in
//            $value = '<a class="' . $key . '" href=mailto:' . $value[0] . '>' . $value[0] . '</a>';
//        }
//        if (is_array($value)) {
//            $value = implode("<br>", $value);
//        }
//        $pattern[0] = "/" . METADATA_PREFIX . "/";
//        $pattern[1] = "/_/";
//        $replacement[0] = "";
//        $replacement[1] = " ";
//        $key = preg_replace($pattern, $replacement, $key);
//        $row = array(strtoupper($key), $value);
//        $rows[] = $row;
//    }
//
//    return $rows;
//}
//
////h_adc_get_metadata}
///*
// * get_metadata}
// */
//
///**
// * h_adc_has_data_access_resource{
// */
//function h_adc_has_data_access_resource($solr_doc) {
//
//    if (isset($solr_doc[METADATA_PREFIX . 'data_access_resource'])) {
//        return 1;
//    }
//    else {
//        return 0;
//    }
//}
//
///**
// * h_adc_has_data_access_resource}
// */

/**
 * h_adc_has_data_access_type{
 */
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

/**
 * h_adc_has_data_access_type}
 */
/*
 * validate the date{
 */
function msb_date_validate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
    //the old way without the date module and popup_date
//  $d = DateTime::createFromFormat($format, $date);
//  return $d && $d->format($format) == $date;
}

/*
 * validate the date}
 */

/*
 * get short iso date{
 */

function msb_get_short_isodate($date) {
    //$dd = array('2011-05-09T12:00:00Z', '2015-02-02T12:00:00Z', '2012-04-13T12:00:00Z');
    $tmp_date = new DateTime($date);
    $short_isodate = $tmp_date->format('Y-m-d');
    return $short_isodate;
}

/*
 * get short iso date}
 */

/*
 * msb_get_iso8601_date_time_zone{
 */

function msb_get_iso8601_date_time_zone($date_string) {
    $tmp_date = new DateTime($date_string);
    $iso8601_date_time_zone = $tmp_date->format('Y-m-d\TH:i:s\Z');
    return $iso8601_date_time_zone;
}

/*
 * msb_get_iso8601_date_time_zone}
 */
/*
 * msb_get_tds_metadata{
 */

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

/*
 * msb_get_tds_metadata}
 */

/*
 * msb_get_tooltip_js{
 * TODO:
 * msb get msb tooltip js
 * This should not be here, but in the JS utill library
 */

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

/*
 * msb_get_tooltip_js}
 */

/*
 * msb_get_skos{
 */

//SKOS definitions should be looked up from an online service, but for now we add them here
//to show in tooltips
//function msb_get_skos($skos_prefLablel) {
//  $skos_definition = "";
////get the skos def xml file
////parse it
////return the definition requested
//  $xml = new SimpleXMLElement(hack_get_skos_opstat());
//
//  $result = $xml->xpath('skos:member');
//
////  
////  $result = $xml->xpath('skos:Collection/skos:prefLabel');
//  while (list(, $node) = each($result)) {
//    //echo '/a/b/c: ',$node,"\n";
//    var_dump($node);
//  }
//  return $skos_definition;
//}

function hack_get_skos_opstat($opstat_name) {
    $opstat_definition = "";
    $hack_opstat_array = array(
      "Operational Status" =>
      "Controlled vocabulary to be used in METAMOD context to describe 
          operational status of datasets handled. This is used in filtering of the
          information. Typically scientific datasets are generated without a
          temporal perspective and review process. The review process of
          scientific products is publications in refereed journals or in data
          citation papers. Experimental, Pre-Operational and Operational are
          concepts supported by institutions with a committment for a specific
          delivery over time.",
      'Operational' =>
      "This is used to tag datasets that are continously being evaluated
          and validated. The production of the data is under the
          responsibility of an authority and follows a review and
          documentation process. Operational data are generated on a
          continous basis with a temporal perspective.",
      'Pre-Operational' =>
      "This is used to describe data that are under review for becoming
          the next operational delivery. It is usually produced continously,
          but not necessarily distributed to users not specifically. It
          covers the intermediate step between Experimental and
          Operational.",
      'Experimental' =>
      "This is used to describe data that belongs to a development and
          production chain that provides operational data. It is the first
          step in the process of becoming operational.",
      'Scientific' =>
      "This is used to describe purely scientific products. that is
          products generated through scientific projects and usually with a
          limited temporal perspective.",
    );
    if (!empty($hack_opstat_array[$opstat_name])) {
        return $hack_opstat_array[$opstat_name];
    }
    return $opstat_definition;
}

/*
 * msb_get_skos}
 */

/*
 * apache_solr_ext{
 * TODO: Apache Solr PHP extension
 * This does not quite work yet. There are PHP/SOLR library issues that 
 * need to be looked into before we can get all the functionality that Apache 
 * SOLR extensions offer.
 */

function test_apache_solr_ext() {

    $options = array
      (
      'hostname' => SOLR_SERVER_HOSTNAME,
      'port' => SOLR_SERVER_PORT,
      'path' => SOLR_SERVER_PATH,
    );

    $client = new SolrClient($options);

    $query = new SolrQuery('mmd_metadata_status:"active"');
    $query->addField('mmd_metadata_status');


    $query->setFacet(true);

    $query->addFacetField('mmd_keywords_keyword')->addFacetField('mmd_iso_topic_category')->setFacetMinCount(1);

    $updateResponse = $client->query($query);

    $response_array = $updateResponse->getResponse();

    $facet_data = $response_array->facet_counts->facet_fields;

//
//  $response = $query_response->getResponse();
// 
//  return  $response;
}

/*
 * apache_solr_ext}
 */

/**
 * DEPRECATED: use adc_add_to_basket in metsis_qsearch_utils.inc
 * msb_add_to_basket{
 * This is currently called from different forms and what is passed in (table selections) 
 * is not consistent. When called from a tableselect listing children, it fails because the
 * selections are passed in as integers which are not useful for finding the dataset in SOLR
 * 
 */
//function msb_add_to_basket(&$form, &$form_state) {
//    global $user;
//    global $metsis_conf;
//    //todo refactor
//    if ($metsis_conf['metsis_basket_authentication_required']['boolean'] === TRUE) {
//        adc_require_login(drupal_get_destination(), $metsis_conf['authentication_default_message']);
//    }
//    if (array_filter($form_state ['values'] ['table'])) {
//        $basket_items = array_filter($form_state ['values'] ['table']);
//        $fields = array(
//          "id",
//          "_version_",
//          //
//          METADATA_PREFIX . "last_metadata_update",
//          METADATA_PREFIX . "personell_email",
//          METADATA_PREFIX . "platform_long_name",
//          METADATA_PREFIX . "data_center_contact_name",
//          METADATA_PREFIX . "collection",
//          METADATA_PREFIX . "geographic_extent_rectangle_east",
//          METADATA_PREFIX . "geographic_extent_rectangle_south",
//          METADATA_PREFIX . "geographic_extent_rectangle_west",
//          METADATA_PREFIX . "geographic_extent_rectangle_north",
//          METADATA_PREFIX . "data_center_data_center_url",
//          METADATA_PREFIX . "platform_short_name",
//          METADATA_PREFIX . "related_information_resource",
//          METADATA_PREFIX . "project_long_name",
//          //data access resource is parsed elsewhere.
//          METADATA_PREFIX . "data_access_resource",
////          METADATA_PREFIX . "data_access_resource_HTTP",
////          METADATA_PREFIX . "data_access_resource_OPeNDAP",
////          METADATA_PREFIX . "data_access_resource_OGC_WMS",
////          METADATA_PREFIX . "data_access_resource_HTTPserver",
//          METADATA_PREFIX . "dataset_production_status",
//          METADATA_PREFIX . "access_constraint",
//          METADATA_PREFIX . "iso_topic_category",
//          METADATA_PREFIX . "temporal_extent_start_date",
//          METADATA_PREFIX . "temporal_extent_end_date",
//          METADATA_PREFIX . "data_center_data_center_name_long_name",
//          METADATA_PREFIX . "dataset_language",
//          METADATA_PREFIX . "data_center_contact_role",
//          METADATA_PREFIX . "data_access_type",
//          METADATA_PREFIX . "project_short_name",
//          METADATA_PREFIX . "abstract",
//          METADATA_PREFIX . "activity_type",
//          METADATA_PREFIX . "keywords_keyword",
//          METADATA_PREFIX . "related_information_type",
//          METADATA_PREFIX . "data_access_wms_layers_wms_layer",
//          METADATA_PREFIX . "operational_status",
//          METADATA_PREFIX . "instrument_long_name",
//          METADATA_PREFIX . "personnel_organisation",
//          METADATA_PREFIX . "data_center_contact_email",
//          METADATA_PREFIX . "instrument_short_name",
//          METADATA_PREFIX . "personnel_role",
//          METADATA_PREFIX . "data_access_description",
//          METADATA_PREFIX . "cloud_cover_value",
//          METADATA_PREFIX . "metadata_identifier",
//          METADATA_PREFIX . "data_center_data_center_name_short_name",
//          METADATA_PREFIX . "metadata_status",
//          METADATA_PREFIX . "personnel_name",
//          METADATA_PREFIX . "title",
//          "bbox",
//        );
//        $my_items = array();
//
//        foreach ($basket_items as $bi) {
////                if (number_of_children($bi) > 0) {
////                    $item_children = mbas_get_children($bi);
////                    metsis_basket_insert($item_children);
////                }
//            $my_items[$bi] = msb_get_fields(SOLR_CORE_PARENT, $bi, $fields);
//            foreach ($my_items[$bi] as $mibi) {
//                if (isset($mibi['error'])) {
//                    drupal_set_message($mibi['error']['msg'], 'error');
//                }
//            }
//            foreach ($my_items[$bi]['response']['docs'] as $doc) {
//
//                if (h_adc_has_data_access_resource($doc) == 0) {
//                    
//                }
//                if (!isset($doc[METADATA_PREFIX . 'data_access_resource'])) {
//                    $message = $bi . " : " . MISSING_DATA_ACCESS_RESOURCE;
//                    drupal_set_message($message, 'warning');
//                    unset($my_items[$bi]);
//                }
//                else {
//                    $doc[METADATA_PREFIX . 'data_access_resource'] = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
//                    $my_items[$bi] = $doc;
//                }
//            }
//        }
//        //reroute to mbas and remove msb_basket_table_insert
//        //
//        //todo5
//        //refactor
//        //todo0 basket items are added multiple times!! This is wrong.
//        $basket_query = adc_get_basket_items($user->uid, 'metadata_identifier');
//        $basket_query_results = $basket_query->fetchAllAssoc('metadata_identifier');
//        $user_basket_content = [];
//        foreach ($basket_query_results as $bqr) {
//            $user_basket_content[] = $bqr->metadata_identifier;
//        }
//
//        $not_in_basket = array_diff_key($my_items, array_flip($user_basket_content));
//        /**
//         * test{
//         */
//        sdpm($my_items);
//        sdpm($user_basket_content);
//        sdpm($not_in_basket);
//        /**
//         * test}
//         */
//        $my_items = $not_in_basket;
//        metsis_basket_insert($my_items);
//        if (count($my_items) > 0) {
//            drupal_set_message(t('The following <b>' . count($my_items) . '</b> datasets have been added to your basket:!values', array(
//              '!values' => theme('item_list', array(
//                'items' => array_keys($my_items)
//                  //'items' => $basket_items
//              ))
//            )));
//        }
//        else {
//            drupal_set_message(t('No new datasets to place in basket.'));
//        }
//    }
//}
//
/*
 * msb_add_to_basket}
 */
/*
 * msb_add_children_to_basket{
 * a workaround function to add children to basket since msb_add_to_basket is not currently 
 * overloaded. msb_add_to_basket should and will be overloade to provide msb_add_children_to_basket
 * functionality
 */

function msb_add_children_to_basket($form, $form_state) {
    //todo refactor
    global $metsis_conf;
    //todo refactor
    //if ($metsis_conf['metsis_basket_authentication_required']['boolean'] === TRUE) {
    //    adc_require_login(drupal_get_destination(), $metsis_conf['metsis_basket_authentication_required']['message']);
    // }
    if (array_filter($form_state ['values'] ['table'])) {
        $basket_items = array_filter($form_state ['values'] ['table']);

        $fields = array(
          "id",
          METADATA_PREFIX . "personnel_email",
          METADATA_PREFIX . "personnel_organisation",
          METADATA_PREFIX . "personnel_name",
          METADATA_PREFIX . "data_access_resource",
          METADATA_PREFIX . "temporal_extent_start_date",
          METADATA_PREFIX . "temporal_extent_end_date",
        );
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
                }

                $my_items[$bi] = $doc;
            }
        }


        metsis_basket_insert(trim_mmd_prefix($my_items));
        drupal_set_message(t('The following datasets have been added to your basket:!values', array(
          '!values' => theme('item_list', array(
            'items' => $basket_items
          ))
        )));
    }
}

/*
 * msb_add_children_to_basket}
 */
/*
 * strip METADATA_PREFIX{
 */

function trim_mmd_prefix($object) {
    $rows = array();
    foreach ($object as $o) {
        $row = array(
          'dataset_id' => $o['id'],
          'personnel_organisation' => $o[METADATA_PREFIX . 'personnel_organisation'],
          'personnel_email' => $o[METADATA_PREFIX . 'personnel_email'],
          'personnel_name' => $o[METADATA_PREFIX . 'personnel_name'],
          'dataset_dar_http' => $o[METADATA_PREFIX . 'data_access_resource']['HTTP'],
          'dataset_dar_ogc_wms' => $o[METADATA_PREFIX . 'data_access_resource']['OGC_WMS'],
//          'dataset_dar_httpserver' => $o[METADATA_PREFIX . 'data_access_resource']['HTTPserver'],
          'dataset_dar_odata' => $o[METADATA_PREFIX . 'data_access_resource']['ODATA'],
          // are opendap url ever defined for child datasets?
          //   'dataset_dar_opendap' => $o[METADATA_PREFIX . 'data_access_resource']['OPeNDAP'],
          'start_date' => $o[METADATA_PREFIX . 'temporal_extent_start_date'],
          'end_date' => $o[METADATA_PREFIX . 'temporal_extent_end_date'],
        );
        $rows[] = $row;
    }
    return $rows;
}

/*
 * strip METADATA_PREFIX}
 */

/**
 * msb_get_fields
 * query SOLR on  field(s)
 */
function msb_get_fields($solr_core, $dataset_id, $fields) {
    $fl = implode(",", $fields);
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . $solr_core . '/select', array(
      "q" => METADATA_PREFIX . "metadata_identifier:" . "\"" . $dataset_id . "\"",
      "wt" => "json",
      "fl" => "$fl",
        )
    );
    return json_decode($res['body'], true);
}

/**
 * end get fields
 */
/*
 * access restrictions{
 */
//function authenticated_users_page_callback_function() {
//    return 'You are an authenticated user!';
//}
/*
 * access restrictions}
 */
/*
 * msb_display_children
 */
//function msb_display_children() {
//    $datasetID = filter_input(INPUT_GET, "datasetID");
//    $children = msb_get_children($datasetID);
//    $header = array("Dataset ID", "Start date", "End date", "Data access");
//    $rows = array();
//
//    foreach ($children['response']['docs'] as $doc) {
//        $pattern = '/' . METADATA_PREFIX . '/';
//        $replacement = '';
//        $id = preg_replace($pattern, $replacement, $doc['id']);
//        $start = preg_replace($pattern, $replacement, $doc['mmd_temporal_extent_start_date']);
//        $end = preg_replace($pattern, $replacement, $doc['mmd_temporal_extent_end_date']);
//        $resouce = preg_replace($pattern, $replacement, $doc['mmd_data_access_resource']);
//
//        $row = array($id, $start, $end, $resouce);
//        $rows[] = $row;
//    }
//    return theme('table', array(
//      'header' => $header,
//      'rows' => $rows,
//      'attributes' => array(
//        'class' => array('ext_data_souce'),
//      )
//        )
//    );
//}

/*
 * end msb_display_children
 */

/**
 * msb_display_children_form{
 */
function msb_display_children_form($form, &$form_state, $datasetID = '') {
    //$datasetID = filter_input(INPUT_GET, "datasetID");

    $children = msb_get_children($datasetID);
    $header = array
      (
      'dataset_id' => t('Dataset ID'),
      'start_date' => t('Start date'),
      'end_date' => t('End date'),
    );
    $rows = array();
    foreach ($children['response']['docs'] as $doc) {
        $dar = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
        $row = array(
          //t('Visit the <a href="@url">settings</a> page', array('@url' => url('admin'))),
          'dataset_id' => l(t($doc['id']), $dar['HTTP']),
          'start_date' => msb_get_short_isodate($doc[METADATA_PREFIX . 'temporal_extent_start_date']),
          'end_date' => msb_get_short_isodate($doc[METADATA_PREFIX . 'temporal_extent_end_date']),
        );
        $rows[$doc['id']] = $row;
    }
    if (BASKET_ELEMENTS_VISIBLE) {
        $form ['table'] = array
          (
          '#type' => 'tableselect',
          '#options' => $rows,
          '#header' => $header,
          '#js_select' => TRUE,
//    '#sticky' => TRUE,
//    '#empty' => t('No datasets found.'),
//    '#multiple' => TRUE,
//    '#pager' => TRUE,
        );
    }
    else {
        $form ['table'] = array
          (
          '#theme' => 'table',
          '#rows' => $rows,
          '#header' => $header,
          '#js_select' => TRUE,
        );
    }
    $form['#validate'][] = 'msb_display_children_validate';
    $form['#submit'][] = 'msb_display_children_submit';
    if (BASKET_ELEMENTS_VISIBLE) {
        $form['submit'] = array
          (
          '#type' => 'submit',
          '#value' => t('Add to basket'),
        );
    }
//TODO: theme pager is no use here since the tabulated data are fetched on the fly.
// the data should be cached and fetched from local db like basket.
    $form ['pager'] = array(
      '#theme' => 'pager',
      '#markup' => theme('pager', array('quantity' => 5))
    );
    return $form;
}

function msb_display_children_validate() {
    //TODO:   
}

function msb_display_children_submit(&$form, &$form_state) {

    msb_add_children_to_basket($form, $form_state);
}

/*
 * msb_display_children_form}
 */

/*
 * msb_count_children{
 * @param type $datasetID
 * @return integer
 */

//
//function msb_count_children($datasetID) {
//    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
//    $res = $con->get('/solr/' . SOLR_CORE_CHILD . '/select', array(
//      "q" => METADATA_PREFIX . "related_dataset:$datasetID",
//      "fl" => METADATA_PREFIX . "related_dataset",
//      "rows" => 0,
//      "wt" => "json",
//      "indent" => "true")
//    );
//
//    $body = json_decode($res['body'], true);
//    if (isset($body['response']['numFound'])) {
//        return $body['response']['numFound'];
//    }
//    return;
//}

/*
 * msb_count_children}
 */

/*
 * msb_concat_data_access_resource{
 */

//function msb_concat_data_access_resource($data_access_resource_array) {
//    $gar = array();
//    foreach ($data_access_resource_array as $dara) {
//
//        /*
//         * test{
//         */
//        $dara = explode(":", $dara, 2);
//        if (!isset($dara[1])) {
//            $dara[1] = "--";
//            $dara[0] = "--";
//        }
//        else {
//            $eara = explode(",", $dara[1]);
//        }
//        if (!isset($eara[1])) {
//            $eara[1] = "--";
//            $eara[0] = "--";
//        }
//        else {
//            $fara = explode(":", $eara[1]);
//        }
//        if (!isset($fara[0])) {
//            $fara[0] = "--";
//            $fara[1] = "--";
//        }
//
//        $patterns = array();
//        $patterns[0] = '/(\s)/';
//        $patterns[1] = '/(\")/';
//        $replacements = array();
//        $replacements[0] = '_';
//        $replacements[1] = '';
//        $dara[0] = preg_replace($patterns, $replacements, $dara[0]);
//        $dara[1] = preg_replace($patterns[1], $replacements[1], $dara[1]);
//        $fara[0] = preg_replace($patterns, $replacements, $fara[0]);
//        $fara[1] = preg_replace($patterns[1], $replacements[1], $fara[1]);
//        $eara[0] = preg_replace($patterns, $replacements, $eara[0]);
//        $eara[1] = preg_replace($patterns[1], $replacements[1], $eara[1]);
//        /**
//         * hack - a few datasets return "URL" on querying  mmd_data_access_resource for OPeNDAP!
//         * This bad metadata. Data providers MUST provide a valid mmd_data_access_resource for each
//         * type defined under  mmd_data_access_type
//         * 
//         */
//        if ($dara[1] == "URL") {
//            //$dara[1] = $dara[0] . " access unavailable";
//            $dara[1] = MISSING_METADATA;
//        }
//        $gar[$dara[0]]['url'] = $eara[0];
//        $gar[$dara[0]][$fara[0]] = $fara[1];
//    }
//
//    return $gar;
//}

/*
 * msb_concat_data_access_resource}
 */
/*
 * get_data_access_http{
 */

//
//function get_data_access_markup($dataset_id, $data_access) {
//    $url = ''
//        . '<div class="botton-wrap ext_data_source">'
////        . '<a class="adc-button adc-sbutton" href="' . $data_access . '" target="_blank">' . $dataset_id . '</a>'
//        . '<a class="adc-button adc-sbutton ext_data_source" href="' . $data_access . '">' . $dataset_id . '</a>'
//        . '</div>';
//    return $url;
//}

/*
 * get_data_access_http}
 */


/*
 * msb_get_children_links{
 */
//
//function msb_get_children_links($dataset_id) {
//    global $base_url;
//    $number_of_children = msb_count_children($dataset_id);
//    if ($number_of_children > 0) {
//        $url = ''
//            . '<div class="botton-wrap">'
////            . '<a class="adc-button adc-sbutton" href="https://' . DRUPAL_SERVER . '/metsis/display/children/' . $dataset_id . '" target="_blank">Children...[' . $number_of_children . ']</a>'
//            . '<a class="adc-button adc-sbutton" href="' . $base_url . '/' . 'metsis/display/children/' . $dataset_id . '" target="_blank">Children...[' . $number_of_children . ']</a>'
//            . '</div>';
//        return $url;
//    }
//}

/*
 * msb_get_children_links}
 */

/*
 * msb_get_md_links{
 */

//function msb_get_md_links($dataset_id) {
//    global $base_url;
//    if (defined('SOLR_METADATA_BUTTON_TEXT')) {
//        $solr_metadata_button_text = SOLR_METADATA_BUTTON_TEXT;
//    }
//    else {
//        $solr_metadata_button_text = "Metadata";
//    }
//    $url = ''
//        . '<div class="botton-wrap">'
//        . '<a class="adc-button adc-sbutton ext_data_source" href="' . $base_url . '/metsis/display/metadata/?core=l1&datasetID=' . $dataset_id . '" >' . $solr_metadata_button_text . '</a>'
////        . '<a class="adc-button adc-sbutton ext_data_source" href="' . $base_url . '/metsis/display/metadata/?datasetID=' . $dataset_id . '" >' . $solr_metadata_button_text . '</a>'
////        . '<a class="adc-button adc-sbutton" href="https://' . DRUPAL_SERVER . '/metsis/display/metadata/?datasetID=' . $dataset_id . '" target="_blank">Metadata</a>'
////        . '<a class="adc-button adc-sbutton colorbox-load" href="https://' . DRUPAL_SERVER . '/metsis/display/metadata/?datasetID=' . $dataset_id . '" width=300&height=500&iframe=true ">Metadata</a>'
//        . '</div>';
//    return $url;
//}

/*
 * msb_get_md_links}
 */
/*
 * msb_get_transfom_link{
 */

function msb_get_fimex_link($dataset_id,$solr_core) {
    //TODO 1
    //
 
   
    
    $url = '';
    $url .= '<div class="botton-wrap">';
    //$url .= '<a class="adc-button adc-sbutton adc-disabled" href="https://' . DRUPAL_SERVER . '/metsis_fimex?dataset_id=' . $dataset_id . '" target="_blank">Transform</a>';
    //$url .= '<a class="adc-button adc-sbutton" href="' . '/metsis_fimex?dataset_id=' . $dataset_id . '" target="_blank">Transform</a>';
    $url .= '<a class="adc-button adc-sbutton" href="' . '/metsis_fimex?dataset_id=' . $dataset_id . '&solr_core=' . $solr_core . '" >Transform</a>';
    //$url .= '<a class="adc-button adc-sbutton" href="' . '/metsis_fimex?dataset_id=' . $dataset_id . '" >Transform</a>';
    //$url .= '<a class="adc-button adc-sbutton adc-disabled">Transform</a>';
    $url .= '</div>';
    return $url;
}

/*
 * msb_get_transform_link}
 */

/*
 * msb_get_opendap_links{
 */

function msb_markup_opendap_links($opendap_extension_urls, $extension_string) {
//
//  $url = $opendap_extension_urls[$extension_string];
//  $od_links = t('<a class="ext_data_source" href="@url"> &lt;OPeNDAP.' . $extension_string . '&gt; </a>', array(
//    '@url' => url($url)
//      )
//  );
//  return $od_links;
//   $url = ''
//      .'<div class="image-wrap">'
//      .'<a class="ext_data_source" '.$opendap_extension_urls[$extension_string].'">'
//      .'<img alt="Transform" src="https://'.DRUPAL_SERVER.'/sites/'.DRUPAL_SERVER.'/files/icons/OPeNDAP-meatball.png">'
//     //.'<p class="image-hover-text">This image looks super neat.</p>'
//      .'</div>';
//      
//return $url;
    /**
     * tests{
     */
    /**
     * tests}
     */
    $url = ''
        . '<div class="botton-wrap">'
        //. '<a class="adc-button adc-sbutton" href="' . $opendap_extension_urls[$extension_string] . '" target="_blank">OPeNDAP[' . $extension_string . ']</a>'
        . '<a class="adc-button adc-sbutton" href="' . $opendap_extension_urls[$extension_string] . '">OPeNDAP[' . $extension_string . ']</a>'
        //the following line works note the https
        //. '<a class="adc-button adc-sbutton colorbox-load" href="https://skandiabanken.no/?width=500&height=500&iframe=true">OPeNDAP[' . $extension_string . ']</a>'
        //the following line does not work since the content being fetched is from an non-https server
        //. '<a class="adc-button adc-sbutton colorbox-load" href="'.$opendap_extension_urls[$extension_string].'?width=500&height=500&iframe=true">OPeNDAP[' . $extension_string . ']</a>'
        . '</div>';

    return $url;
}

/**
 * test{
 */
// this is not a secure function and should not be used.
//function ob_ext_test($ext_url){
//    include_once $ext_url;
//    $output= ob_get_contents();
//    ob_end_clean();
//    return $output;
//}
/**
 * tset}
 */
/*
 * msb_get_md_links}
 */

/**
 * basket
 * 1. the user performs search and discovery via metadata search interface
 * 2. user choses datasets to add to basket
 * 3. user visits basket to finalize transaction
 * 4. user request download or tranfrom followed by download
 * 
 */
//http://10.99.3.30:8080/basketService?userId=nemo&email=mins@met.no&format=tgz&uri=http://thredds.met.no/thredds/dodsC/arome25/arome_metcoop_default2_5km_latest.nc&fimex=projection=%2Bproj%3Dstere+%2Blat_0%3D90+%2Blat_ts%3D71+%2Blon_0%3D0+%2Bk%3D1+%2Bx_0%3D0+%2By_0%3D0+%2Bdatum%3DWGS84+%2Bunits%3Dm+%2Bno_defs;interpolation=nearestneighbor;variables=air_temperature_2m,relative_humidity_2m;dateFrom=2016-03-08%2000:00:00%20UTC;dateTo=2016-03-09%2000:00:00%20UTC;xAxisFrom=-405273.4375;xAxisTo=1523437.5;yAxisFrom=-3789062.5;yAxisTo=-1665039.0625;steps=100

/**
 * msb_get_feature_type
 * parses OpeNDAP Data Attribute Structure to look for given feature_type
 * For now, the main purpose is to look for featureType "timeSeries"
 * This is probably not the most reliable way since DAS info is not mandetory
 * However, we display a timeSeries plot if this feature time is specified.
 * Example OpeNDAP DAS URL:
 * http://thredds.nersc.no/thredds/dodsC/normap/barents4km-bloom-spring-indicators/barents4km-bloom-spring-indicators-zone02-19980101-20141231.nc.das
 */
function msb_get_feature_type($opendapURL, $featureType) {
    //TODO: using the .das is too resouce intensive since it needs custom parsing
    // feature type must be made avilable in .ddx
    $opendapURL = $opendapURL . ".das";
    $res = drupal_http_request($opendapURL);
}

/**
 * mbas_get_children (metsis baseket get data on child/level 2 datasets for storage in mbas)
 */

/**
 * mbas_get_children
 */
function mbas_get_children($datasetID) {
    $children = msb_get_children($datasetID);
    $rows = array();
    foreach ($children['response']['docs'] as $doc) {
        $dar = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
        $row = array(
          'dataset_id' => $doc['id'],
          'personnel_organisation' => $doc[METADATA_PREFIX . 'personnel_organisation'],
          'personnel_email' => $doc[METADATA_PREFIX . 'personnel_email'],
          'personnel_name' => $doc[METADATA_PREFIX . 'personnel_name'],
          'dataset_dar_http' => $dar['HTTP'],
          'dataset_dar_ogc_wms' => $dar['OGC_WMS'],
          'dataset_dar_odata' => $dar['ODATA'],
//          'dataset_dar_httpserver' => $dar['HTTPserver'],
          'start_date' => msb_get_short_isodate($doc[METADATA_PREFIX . 'temporal_extent_start_date']),
          'end_date' => msb_get_short_isodate($doc[METADATA_PREFIX . 'temporal_extent_end_date']),
        );
        $rows[] = $row;
    }
    return $rows;
}

function number_of_children($datasetID) {
    $fields = "numFound";
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_CHILD . '/select', array(
      "q" => METADATA_PREFIX . "related_dataset:$datasetID",
      "wt" => "json",
      "fl" => $fields,
        )
    );
    $children = json_decode($res['body'], true);
    return $children['response']['numFound'];
}

/*
 * go_to_start{
 */

function go_to_start() {
    drupal_goto("metadata_search");
}

/*
 * go_to_start}
 */

/*
 * validators{
 */

function adc_longitude_validate() {
    return;
}

function adc_latitude_validate() {
    return;
}

/*
 * validators}
 */

/*
 * adc_keywords_to_string{
 */

function adc_keywords_to_string($keywords_array) {
    $glue = "<br>";
    return implode($glue, $keywords_array);
}

/*
 * adc_keywords_to_sting}
 */
/*
 * adc_goto_basket{
 */

function adc_goto_basket() {
    global $user;
    global $metsis_conf;
    if (!isset($metsis_conf['basket_endpoint'])) {
        drupal_set_message("No basket view defined. You need to create a basket view and set the variable \$metsis_conf['basket_endpoint'] as administrator first.", 'warning');
    }
    else {
        //drupal_goto($metsis_conf['basket_endpoint'] . "/" . $user->uid);
        drupal_goto($metsis_conf['basket_endpoint']);
    }
    return;
}

/*
 * adc_goto_basket}
 */
/*
 * adc_empty_basket{
 */

function adc_empty_basket() {
    global $user;
    global $metsis_conf;
    //todo
    //refactor
    $table = 'metsis_basket';

    if (!isset($metsis_conf['basket_endpoint'])) {
        drupal_set_message("No basket view defined. You need to create a basket view and set the variable \$metsis_conf['basket_endpoint'] as administrator first.", 'warning');
    }
    else {

        db_delete($table)
            ->condition('uid', $user->uid)
            ->execute();
    }
    return;
}

/*
 * adc_empty_basket}
 */

/**
 * 
 * @global type $metsis_conf
 * @param type $form_state
 * @return boolean
 * adc_limit_empty_search{
 */
function adc_limit_empty_search($form_state) {
    global $metsis_conf;
//
//
//    if ($metsis_conf['investigator_visible'] === TRUE) {
//        $number_of_fields_to_check += 1;
//        if (empty($form_state['values']['chosen_investigator']) || is_null($$form_state['values']['chosen_investigator'])) {
//            $number_of_empty_fields += 1;
//        }
//    }
//
//    if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
//        $number_of_fields_to_check += 1;
//        if (empty($form_state['values']['cloud_cover_value']['chosen_cloud_cover_value']) || is_null($form_state['values']['cloud_cover_value']['chosen_cloud_cover_value'])) {
//            $number_of_empty_fields += 1;
//        }
//    }


    $number_of_empty_fields = 0;
    $number_of_fields_to_check = 0;

    if ($metsis_conf['search_form_fields_empty_check']['chosen_full_text_search'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['chosen_full_text_search']) || is_null($form_state['values']['chosen_full_text_search'])) {
            $number_of_empty_fields += 1;
        }
    }
    if ($metsis_conf['search_form_fields_empty_check']['chosen_topics_and_variables_a'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['chosen_topics_and_variables_a']) || is_null($form_state['values']['chosen_topics_and_variables_a'])) {
            $number_of_empty_fields += 1;
        }
    }
    if ($metsis_conf['search_form_fields_empty_check']['cloud_cover_value']['chosen_cloud_cover_value'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['cloud_cover_value']['chosen_cloud_cover_value']) || is_null($form_state['values']['cloud_cover_value']['chosen_cloud_cover_value'])) {
            $number_of_empty_fields += 1;
        }
    }
    if ($metsis_conf['search_form_fields_empty_check']['finished_after'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['finished_after']) || is_null($form_state['values']['finished_after'])) {
            $number_of_empty_fields += 1;
        }
    }
    if ($metsis_conf['search_form_fields_empty_check']['finished_before'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['finished_before']) || is_null($form_state['values']['finished_before'])) {
            $number_of_empty_fields += 1;
        }
    }
    if ($metsis_conf['search_form_fields_empty_check']['bbox_top_left_lon'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['bbox_top_left_lon']) || is_null($form_state['values']['bbox_top_left_lon'])) {
            $number_of_empty_fields += 1;
        }
    }
    if ($metsis_conf['search_form_fields_empty_check']['bbox_top_left_lat'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['bbox_top_left_lat']) || is_null($form_state['values']['bbox_top_left_lat'])) {
            $number_of_empty_fields += 1;
        }
    }
    if ($metsis_conf['search_form_fields_empty_check']['bbox_bottom_right_lon'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['bbox_bottom_right_lon']) || is_null($form_state['values']['bbox_bottom_right_lon'])) {
            $number_of_empty_fields += 1;
        }
    }
    if ($metsis_conf['search_form_fields_empty_check']['bbox_bottom_right_lat'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['bbox_bottom_right_lat']) || is_null($form_state['values']['bbox_bottom_right_lat'])) {
            $number_of_empty_fields += 1;
        }
    }
    if ($metsis_conf['search_form_fields_empty_check']['chosen_investigator'] === TRUE) {
        $number_of_fields_to_check += 1;
        if (empty($form_state['values']['chosen_investigator']) || is_null($$form_state['values']['chosen_investigator'])) {
            $number_of_empty_fields += 1;
        }
    }
    if (($metsis_conf['limit_empty_search'] === TRUE) && ($number_of_fields_to_check === $number_of_empty_fields)) {
        return TRUE;
    }
    else {
        return FALSE;
    }
}

/**
 * adc_limit_empty_search}
 */
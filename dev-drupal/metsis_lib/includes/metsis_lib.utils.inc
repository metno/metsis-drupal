<?php

include_once drupal_get_path('module', 'metsis_lib') . '/includes/metsis_lib.constants.inc';
include_once drupal_get_path('module', 'metsis_qsearch') . '/metsis_qsearch.constants.inc';

function adc_require_login($calling_uri, $message) {

    global $user;
    /**
     * debug test{
     */
//    $e = new \Exception;
//    sdpm($e->getTraceAsString());
    /**
     * debug test}
     */
//if (!($user->uid)) {
    if (!user_is_logged_in()) {
        drupal_set_message("<b>" . t($message) . "</b>", 'warning');
        $dest = drupal_get_destination();
//drupal_goto('user/login', array('query' => array('destination' => $GET['q'])));
//drupal_goto('user/login', array('query' => drupal_get_destination()));
        drupal_goto('user/login', array('query' => $calling_uri));
//drupal_goto('user/login', $dest);
    }
}

function in_array_r($needle, $haystack, $strict = true) {
    foreach ($haystack as $value) {
        if (( $strict ? $value === $needle : $value == $needle ) || ( is_array($value) && in_array_r($needle, $value, $strict))) {
//return true;
            return $needle;
        }
    }
    return false;
}

//
//
//function in_array_r($needle, $haystack, $strict = false) {
//    foreach ($haystack as $item) {
//        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
//            return true;
//        }
//    }
//
//    return false;
//}
//

/**
 * Searches haystack for needle and 
 * returns an array of the key path if 
 * it is found in the (multidimensional) 
 * array, FALSE otherwise.
 *
 * @mixed array_search_recursive ( mixed needle, 
 * array haystack [, bool strict[, array path]] )
 */
function array_search_recursive($needle, $haystack, $strict = false, $path = array()) {
    if (!is_array($haystack)) {
        return false;
    }

    foreach ($haystack as $key => $val) {
        if (is_array($val) && $subPath = array_search_recursive($needle, $val, $strict, $path)) {
            $path = array_merge($path, array($key), $subPath);
            return $path;
        }
        elseif ((!$strict && $val == $needle) || ($strict && $val === $needle)) {
            $path[] = $key;
            return $path;
        }
    }
    return false;
}

//DOES NOT WORK currently. Not completed yet.
//hack function to fetch a variable value from an XML OPeNDAP endpoint. 
//this function should be used only when no alternative is avaible
//it uses ncdump!

function adc_get_netcdf_variable_value($key, $od_url) {
    $key = "proj4_string";
    $od_url = "http://thredds.met.no/thredds/dodsC/osisaf/met.no/ice/emis/2014/08/ice_emis_sh_stere-100_ssmis_201408261200.nc";
    $output = shell_exec("ncdump -x $od_url");
    $xml = new SimpleXMLElement($output);
    $json = drupal_json_decode(drupal_json_encode($xml));
    $key_path = array_search_recursive($key, $json);
    $key_size = count($key_path);
    $fully_qualified_key = "";
    for ($i = 0; $i < $key_size - 1; $i++) {
        $fully_qualified_key .= "[" . $key_path[$i] . "]";
    }
    $fully_qualified_value = '$json' . $fully_qualified_key . "['value']";
    $fully_qualified_value = (array) $fully_qualified_value;
}

function get_metsis_db_records($table, $pkey_array, $fields_array) {
    $records = [];
    foreach ($pkey_array as $pk) {
        $query = db_select($table, 't');
        $query->fields('t', $fields_array);
        $query->condition('iid', $pk);
        $result = $query->execute()->fetch();
        foreach ($fields_array as $f) {
//drupal_set_message($result->$f);
            $records[$pk][$f] = $result->$f;
        }
    }
    return $records;
}

/**
 * get_metsis_date(){
 */
function get_metsis_date($date_string, $format) {
    $d = new DateTime($date_string);

//$d = new DateTime('2012-03-05T12:00:00Z');
//$d = new DateTime();
//$dint=$d->getTimestamp();
//sdpm($d);
//sdpm($dint);

    return $d->format($format);
}

/**
 * get_metsis_date()}
 */
/*
 * adc_get_wms_get_capabilities{
 */

function adc_get_wms_get_capabilities($uri) {
    $result = drupal_http_request($uri);
    $data = $result->data;
    $xml = simplexml_load_string($data);
    $json = json_encode($xml);
    $array = json_decode($json, TRUE);
    return json_decode($json, TRUE);
}

/*
 * adc_get_wms_get_capabilities}
 */

/**
 * breadcrumb() {
 * @param type $tree
 * @param type $needle
 * @param type $result
 * @return boolean
 * 
 */
//TODO: this fails for OPeNDAP since the value we are 
//searching for can be on another branch to the name of the varible 
// need to search for value based on regex
// This is likely to be very slow, but our OPeNDAP streams seem to be very
// heterogeneous!
function breadcrumb($tree, $needle, &$result = array()) {

    $result = array();

    if (is_array($tree)) {
        foreach ($tree as $node) {
            if ($node['title'] == $needle) {
                $result[] = $node['title'];
                echo '1-';
                return true;
            }
            else if (!empty($node['nodes'])) {
                if (breadcrumb($node['nodes'], $needle, $result)) {
                    echo '2-';
                    $result[] = $node['title'];
                    return true;
                }
            }
        }
    }
    else {
        if ($tree == $needle) {
            echo '3-';
            $result[] = $tree;
            return true;
        }
    }
    return false;
}

/**
 * e.g. 
 * breadcrumb($od_data, 'proj4', $result);
 * print_r($result);
 */
/**
 * breadcrumb() }
 */
//    $opendap_ddx = $dar[0]['OPeNDAP']['url'] . ".ddx";
//
//    //test{
//    $feature_types = adc_get_od_feature_type($opendap_ddx);
//    //sdpm($feature_types);
//    foreach ($feature_types['Attribute'] as $a) {
//        foreach ($a['Attribute'] as $aa) {
//            if (isset($aa['value'])) {
//                if ($aa['value'] == 'timeSeries') {\
//                    //we have feature type time series. Do the markup 
//                    sdpm($aa['value']);
//                }
//            }
//        }
//    }

/**
 * adc_get_metadata_item{
 * @param type $metadata_identifier
 * @param type $metadata_item
 * @return type
 */
//function adc_get_metadata_item($metadata_identifier, $metadata_item) {
function adc_get_metadata_item($solr_core, $metadata_identifier, $metadata_item) {
    global $metsis_conf;
    $con = new HttpConnection($metsis_conf['solr_server_ip'], $metsis_conf['solr_server_port']);
    //$res = $con->get('/solr/' . $metsis_conf['solr_core_parent'] . '/select', array(
    $res = $con->get('/solr/' . $solr_core . '/select', array(
      "q" => $metsis_conf['metadata_prefix'] . "metadata_identifier:" . '"' . $metadata_identifier . '"',
      "fl" => $metadata_item,
      "wt" => "json",
      "indent" => "true")
    );
    $body = json_decode($res['body'], true);
    return $body['response']['docs'];
}

/**
 *  adc_get_metadata_item}
 */

/**
 * adc_get_data_access_resource{
 * @global type $metsis_conf
 * @param type $metadata_identifier
 */
function adc_get_data_access_resource($solr_core, $metadata_identifier) {
    global $metsis_conf;

    //$raw = adc_get_metadata_item($metadata_identifier, $metsis_conf['metadata_prefix'] . "data_access_resource");
    $raw = adc_get_metadata_item($solr_core, $metadata_identifier, $metsis_conf['metadata_prefix'] . "data_access_resource");
    $my_data_access_resource = [];
    foreach ($raw as $key => $value) {
        foreach ($value as $k => $v) {
            foreach ($v as $kk => $vv) {
                $la = explode(':', $vv, 2);
                $protocol = str_replace('"', '', $la[0]);
                $ma = explode(',', $la[1], 2);
                $uri = str_replace('"', '', $ma[0]);
                $na = explode(':', $ma[1], 2);
                $description_name = str_replace('"', '', $na[0]);
                $description_value = str_replace('"', '', $na[1]);
                $my_data_access_resource[$protocol]['uri'] = $uri;
                $my_data_access_resource[$protocol][$description_name] = $description_value;
            }
        }
    }
    return($my_data_access_resource);
}

/**
 * adc_get_data_access_resource}
 */

/**
 * adc_parse_data_access_resource{
 */
function adc_parse_data_access_resource($data_access_resource) {
    $dar = [];
    foreach ($data_access_resource as $k => $v) {
        $va = explode(',', $v);
        $pu = explode(':', $va[0], 2);
        $dt = explode(':', $va[1], 2);
        $protocol = str_replace('"', '', $pu[0]);
        $dar[$protocol]['uri'] = str_replace('"', '', $pu[1]);
        $dar[$protocol]['description'] = str_replace('"', '', $dt[1]);
    }
    return($dar);
}

/**
 * adc_parse_data_access_resource}
 */
/*
 * deprecated. not used any longer and can be removed.
 * adc_get_od_feature_type($opendap_ddx){
 */
//
//function adc_get_od_feature_type($opendap_ddx) {
//
//    $ddx = drupal_http_request($opendap_ddx);
//    if ($ddx->code == '200') {
//        $od_data = new SimpleXMLElement($ddx->data);
//        return drupal_json_decode(drupal_json_encode($od_data));
//    }
//    else {
//        //todo refactor
//        drupal_set_message("Failed to fetch (OPeNDAP) data from data server", 'error');
//        //drupal_goto('metadata_search');
//    }
//    return;
//}

/*
 * adc_get_od_feature_type}
 */

/**
 * deprecated. Not used any longer and can be removed.
 * TODO: this is to be replaced by a request to SOLR tumbnail core instead
 *       1. check if dataset has featureType
 *       2. the "featureType service" must provided with the necessary parameters
 *          for plotting, featureType
 *       3. Do we assume that the OPeNDAP URI is the same for all featureTypes?
 *       
 * @param type $opendap_uri
 * @return int
 */
//has_timeSeries(){
//function adc_has_timeSeries($opendap_uri) {
//    foreach (adc_get_od_feature_type($opendap_uri . ".ddx")as $a) {
//        if (isset($a['Attribute'])) {
//            foreach ($a as $aa) {
//                foreach ($aa as $aaa) {
//                    if (isset($aaa['value'])) {
//                        if ($aaa['value'] === 'timeSeries') {
//                            //sdpm($aaa['value']);
//                            return 1;
//                        }
//                    }
//                }
//            }
//        }
//        if (isset($a[0])) {
//            foreach ($a as $aa) {
//                foreach ($aa['Attribute'] as $aaa) {
//                    if (isset($aaa['value'])) {
//                        if ($aaa['value'] === 'timeSeries') {
//                            //sdpm($aaa['value']);
//                            return 1;
//                        }
//                    }
//                }
//            }
//        }
//    }
//    return 0;
//}
/**
 * adc_has_timeSeries}
 */

/**
 * adc_has_feature_type{
 * @param type $metadata_identifier
 * @param type $ft
 * @return int
 */
function adc_has_feature_type($metadata_identifier, $ft) {
    $feature_type = adc_get_feature_type($metadata_identifier);
    if ($feature_type === $ft) {
        return 1;
    }
    return 0;
}

/**
 * adc_has_feature_type}
 */

/**
 * adc_get_feature_type{
 */
function adc_get_feature_type($metadata_identifier) {

    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_CONFIG . '/select', array(
      "q" => METADATA_PREFIX . "metadata_identifier:" . '"' . $metadata_identifier . '"',
      "fl" => "feature_type",
      "wt" => "json",
      "indent" => "true")
    );
    $body = json_decode($res['body'], true);
    if ($body['response']['numFound'] > 0) {
        if (isset($body['response']['docs'][0]['feature_type'])) {
            return $body['response']['docs'][0]['feature_type'];
        }
    }
    return 0;
}

/**
 * adc_get_featureType}
 */

/**
 * adc_get_collection{
 * @param type $collection
 * @return string
 */
function adc_get_collections($collections) {

    $words = explode(",", $collections);
    array_walk($words, 'trimV');
    array_walk($words, 'dquoteV');
    $q = METADATA_PREFIX . "collection:(" . implode(" OR ", $words) . ")";
    return $q;
}

/**
 * adc_get_collection}
 */

/**
 * trimV{
 * @param type $v
 * trim values by reference. 
 * used for trimming array elements among other things
 */
function trimV(&$v) {
    $v = trim($v);
}

/**
 * trimV}
 */

/**
 * dquoteV{
 * @param type $v
 * Double quote values by reference. 
 * used for double quoting array elements among other things
 */
function dquoteV(&$v) {
    $v = '"' . $v . '"';
}

/**
 * trimV}
 */

/**
 * adc_get_od_variables{
 * deprecated
 * use adc_get_od_global_attributes and adc_get_od_variables instead
 */
//function deprecated_adc_get_od_variables($od_data_object) {
//
//    $od_vars = array();
//    $counter = 0;
//    foreach ($od_data_object['Grid'] as $dg) {
//        if (isset($dg['@attributes']['name'])) {
//            $od_vars[$counter]['name'] = $dg['@attributes']['name'];
//        }
//        else {
//            $od_vars[$counter]['name'] = "";
//        }
//        foreach ($dg['Attribute'] as $dga) {
//            $od_vars[$counter][$dga['@attributes']['name']] = isset($dga['value']) ? $dga['value'] : "";
//        }
//        $counter = $counter + 1;
//    }
//    return $od_vars;
//}

/**
 * 
 * @param type $rir
 * @return type
 * adc_parse_related_information{
 */
function adc_parse_related_information_resource($rir) {
//parse the string each element consists of
//each element has 3 parts: mmd_related_information_type:URI:description
//with lots of other escaped and none escaped characters. These have to be
//custom parsed. The MMD spec specifies the following Related Information types
//under section 5.15
//"Project home page" - URI to the project home page generating the data.
//"Users guide" - URI to a users guide or product manual for the dataset.
//"Dataset landing page" - A dataset landing page. 
//"Extended metadata"
//adc_parse_related_information($rir){}
    $related_information_resource = [];
    foreach ($rir as $rir_elem) {
        $la = explode(':', $rir_elem, 2);
        $protocol = str_replace('"', '', $la[0]);
        $ma = explode(',', $la[1], 2);
        $uri = str_replace('"', '', $ma[0]);
        $na = explode(':', $ma[1], 2);
        $description_name = str_replace('"', '', $na[0]);
        $description_value = str_replace('"', '', $na[1]);
        $related_information_resource[$protocol]['uri'] = $uri;
        $related_information_resource[$protocol][$description_name] = $description_value;
    }
    return $related_information_resource;
}

/**
 * adc_parse_related_information}
 */

/**
 * adc_get_button_markup{
 */
function adc_get_button_markup($metadata_identifier, $button_uri) {
    $url = ''
        . '<div class="botton-wrap">'
        . '<a class="adc-button adc-sbutton" href="' . $button_uri . '">' . $metadata_identifier . '</a>'
        . '</div>';
    return $url;
}

/**
 * adc_get_button_markup}
 */
/*
 * get_metadata{
 * todo 8
 * refactor and use adc_parse_solr_mmd_type_one() etc.
 */

function adc_get_metadata() {

    global $metsis_conf;
//$datasetID = filter_input(INPUT_GET, "datasetID");
    $metadata_sort_order = $metsis_conf['metadata_sort_order'];
    $page_inputs = drupal_get_query_parameters();
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);

    if ($page_inputs['core'] == 'l1') {
        $core = SOLR_CORE_PARENT;
    }
    if ($page_inputs['core'] == 'l2') {
        $core = SOLR_CORE_CHILD;
    }
    $res = $con->get('/solr/' . $core . '/select', array(
//$res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array(
// "q" => "id:$datasetID",
//      "q" => "id:" . $page_inputs['datasetID'],
      "q" => METADATA_PREFIX . "metadata_identifier:" . '"' . $page_inputs['datasetID'] . '"',
      "wt" => "json",
      "indent" => "true")
    );
    $body = json_decode($res['body'], true);
    if (array_filter($body['response']['docs'][0])) {
//get list of element from $global (set in metsis.settings.php)
//$metadata = array_filter($body['response']['docs'][0]);
        $metadata = array_filter($body['response']['docs'][0]);
//the value indexed in SOLR should be used without the need for change
//this is costly.
        if (isset($metadata[METADATA_PREFIX . 'cloud_cover_value'])) {
//todo00 refactor $metsis_conf to metsis_lib.constants.inc
            $metadata[METADATA_PREFIX . 'cloud_cover_value'] = number_format($metadata[METADATA_PREFIX . 'cloud_cover_value'], $metsis_conf['results_number_decimal_display_format']);
        }
        $zmetadata = array_intersect_key($metadata, array_flip($metsis_conf['metadata_visible']));
        $metadata = $zmetadata;

        $header = array('Metadata key', 'Metadata value');

//split the array into top and bottom
//top is sorted according to $metsis_conf['metadata_sort_order']
//bottom is sorted alphabetically
        $top_array = array();
        $bottom_array = array();
        foreach ($metadata as $k => $v) {
            if (in_array($k, $metadata_sort_order)) {
                $top_array[$k] = $v;
            }
            else {
                $bottom_array[$k] = $v;
            }
        }
        $sorted_top_array = array();
        foreach (array_values($metadata_sort_order) as $key) {
//skip MMD elements that are in $metsis_conf['metadata_sort_order']
//but not in the metadata
//TODO must distintuish between mandatory and optional elements
//since the absence of the latter should not cause error
            if (key_exists($key, $top_array)) {
                $sorted_top_array[$key] = $top_array[$key];
            }
        }
        ksort($bottom_array);
        $top_rows = h_adc_get_metadata($sorted_top_array);
        $bottom_rows = h_adc_get_metadata($bottom_array);
        $rows = array_merge($top_rows, $bottom_rows);

//$final_themed_content = 
        /**
         * test{
         */
        $metadata_table = theme('table', array(
          'header' => $header,
          'rows' => $rows,
          'attributes' => array(
            'class' => array('ext_data_souce'),
          ),
        ));
        return theme('metadata', array('metadata_table' => $metadata_table));
//        return theme('metadata', array('metadata_table' => array(
//            'header' => $header,
//            'rows' => $rows,
//            'attributes' => array(
//              'class' => array('ext_data_souce'),
//            ),
//          )
//            )
//        );
        /**
         * test}
         */
////
//        return theme('table', array(
//          'header' => $header,
//          'rows' => $rows,
//          'attributes' => array(
//            'class' => array('ext_data_souce'),
//          ),
//            )
//        );
    }
}

//h_adc_get_metadata{
//"h"_elper function for adc_get_metadata
//This ties the code tightly with the structure of the data elements in SOLR.
//It is hard to avoid given the requirements of the project and the way 
//the data is indexed in SOLR. 
//A much cleaner and more generic approach would be possible if the data 
//could be indexed in standard structures, such as single or multidimensional
//(associative) arrays in SOLR.

function h_adc_get_metadata($metadata) {
    $rows = array();
    foreach ($metadata as $key => $value) {
        if ($key == METADATA_PREFIX . "data_access_resource") {
            foreach ($value as $k => $v) {
                $la = explode(":", $v, 2);
                $ma = explode(",", $la[1]);
                $na = explode(":", $ma[1]);

                $protocol = str_replace('"', '', $la[0]);
                switch ($protocol) {
                    case "OPeNDAP";
                        $url_ext = ".html";
                        break;
                    case "OGC WMS";
                        $url_ext = "?SERVICE=WMS&REQUEST=GetCapabilities";
                        break;
                    default:
                        $url_ext = "";
                        break;
                }
                $value[$k] = $protocol . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
//$value[$k] = $na[1] . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
            }
        }
        if ($key == METADATA_PREFIX . "related_information_resource") {
            foreach ($value as $k => $v) {
                $la = explode(":", $v, 2);
                $ma = explode(",", $la[1]);
                $na = explode(":", $ma[1]);

                $protocol = str_replace('"', '', $la[0]);
                switch ($protocol) {
                    default:
                        $url_ext = "";
                        break;
                }
                $value[$k] = $protocol . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
//$value[$k] = $na[1] . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
            }
        }
        if ($key == METADATA_PREFIX . "data_center_data_center_url") {
            $value = '<a class="' . $key . '" href=' . $value . '>' . $value . '</a>';
        }
        if ($key == METADATA_PREFIX . "data_center_contact_email") {

            $value = '<a class="' . $key . '" href=mailto:' . $value . '>' . $value . '</a>';
        }
        if ($key == METADATA_PREFIX . "personnel_email") {
//todo 5 
//this field can have multiple values.
//need to link all emails in
            $value = '<a class="' . $key . '" href=mailto:' . $value[0] . '>' . $value[0] . '</a>';
        }
        if (is_array($value)) {
            $value = implode("<br>", $value);
        }
        $pattern[0] = "/" . METADATA_PREFIX . "/";
        $pattern[1] = "/_/";
        $replacement[0] = "";
        $replacement[1] = " ";
        $key = preg_replace($pattern, $replacement, $key);
        $row = array(strtoupper($key), $value);
        $rows[] = $row;
    }

    return $rows;
}

//h_adc_get_metadata}
/*
 * get_metadata}
 */

/**
 * h_adc_has_data_access_resource{
 */
function h_adc_has_data_access_resource($solr_doc) {

    if (isset($solr_doc[METADATA_PREFIX . 'data_access_resource'])) {
        return 1;
    }
    else {
        return 0;
    }
}

/**
 * h_adc_has_data_access_resource}
 */

/**
 * 
 * @param type $object
 * @return type
 */
function object2array($object) {
    return @json_decode(@json_encode($object), 1);
}

/**
 * 
 */

/**
 * hack_remove_xml_namespace(){
 * PHP SimpleXML Parser not tackle name space delimeter ":" (colon)
 * as a hack for the WPS service we replace ":" with "_" (colon with underscore) 
 */
function hack_xml_namespace($xml) {
    return preg_replace('~(</?|\s)([a-z0-9_]+):~is', '$1$2_', $xml);
}

/**
 * hack_remove_xml_namespace()}
 */

/**
 * adc_get_wps_array(){
 */
function adc_get_wps_array($wps_xml) {
    return object2array(simplexml_load_string(hack_xml_namespace($wps_xml)));
}

/**
 * adc_get_wps_array()}
 */

/**
 * generate_uuid() {
 * @return type
 */
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0C2f) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0x2Aff), mt_rand(0, 0xffD3), mt_rand(0, 0xff4B)
    );
}

/**
 * generate_uuid() }
 */

/**
 * adc_get_random_file_name{
 */
function adc_get_random_file_name() {
    $fn = "" . time() . "-" . generate_uuid() . "";
    return $fn;
}

/**
 * adc_get_random_file_name}
 */

/**
 * adc_get_od_global_attributes{
 */
function adc_get_od_global_attributes($metadata_identifier, $collection_core) {
    $odquery = '{
                findAllAttributes(
                  datasetId: "' . $metadata_identifier . '", collection: "' . $collection_core . '"
                    ) {
                        name value
                        
                    }
               }';
    $con = new HttpConnection(METSIS_OPENDAP_PARSER_IP, METSIS_OPENDAP_PARSER_PORT);
    $res = $con->get(METSIS_OPENDAP_PARSER_SERVICE, array(
      "query" => $odquery,
        )
    );
    $jres = json_decode($res['body'], true);
    return $jres;
}

/**
 * adc_get_od_global_attributes}
 */

/**
 * adc_get_od_variables{
 * @param type $metadata_identifier
 * @param type $collection_core
 * @return type
 */
function adc_get_od_variables($metadata_identifier, $collection_core) {
    $odquery = '{
                findAllVariables(
                  datasetId: "' . $metadata_identifier . '", collection: "' . $collection_core . '"
                    ) {
                        name
                             attributes {
                               name value
                                }
                    }
               }';

    $con = new HttpConnection(METSIS_OPENDAP_PARSER_IP, METSIS_OPENDAP_PARSER_PORT);
    $res = $con->get(METSIS_OPENDAP_PARSER_SERVICE, array(
      "query" => $odquery,
        )
    );
//sdpm($odquery);
    $jres = json_decode($res['body'], true);
//    //sdpm($oquery_res);
//    foreach ($oquery_res['data']['findAllVariables'] as $dfav) {
//        //var_dump($dfav['name']);
//        foreach ($dfav['attributes'] as $dfavatt) {
//
//            if ($dfavatt['name'] === 'standard_name') {
//                //sdpm($dfavatt['value']);
//            }
//            if ($dfavatt['name'] === 'long_name') {
//                //sdpm($dfavatt['value']);
//            }
//            if ($dfavatt['name'] === 'units') {
//                //sdpm($dfavatt['value']);
//            }
//        }
//    }
    return $jres;
}

/**
 * adc_get_od_variables}
 */

/**
 * adc_get_od_ga{
 * expects as input what is returned by adc_get_od_global_attributes()
 * @param type $od_global_attributes_array
 * @param type $attribute_name
 * @return type
 */
function adc_get_od_ga($od_global_attributes_array, $attribute_name) {
    $this_odga = array();
    foreach ($od_global_attributes_array as $odgaa) {
        if ($odgaa['name'] == $attribute_name) {
            $this_odga[trim($odgaa['name'])] = $odgaa['value'];
        }
    }
    return $this_odga;
}

/**
 * adc_get_od_ga}
 */

/**
 * 
 * adc_get_odv_standard_names{
 * @global type $metsis_conf
 * @param type $metadata_identifier
 * @return type
 */
function adc_get_odv_standard_names($odv_object, $exclude_vars_array) {
    $odv_standard_names = [];
    foreach ($odv_object as $odvo) {
        if (key_exists('standard_name', $odvo)) {
            if (in_array(trim($odvo['standard_name']), $exclude_vars_array)) {
                continue;
            }
            $odv_standard_names[$odvo['standard_name']] = $odvo['standard_name'];
        }
    }
    return $odv_standard_names;
}

/**
 * adc_get_odv_standard_names}
 */

/**
 * adc_get_odv_object{
 */
function adc_get_odv_object($opendap_variables) {
    global $metsis_conf;
    $odv_object = array();
    foreach ($opendap_variables as $odv) {
        $vn = trim($odv['name']);
        $odv_object[$vn] = $vn;
        $kvpairs = [];
        foreach ($odv['attributes'] as $odva) {
            $odvan = trim($odva['name']);
            $odvav = trim($odva['value']);
            if ($odva['name'] == 'long_name') {
                $kvpairs[$odvan] = $odvav;
            }
            if ($odva['name'] == 'standard_name') {
                $kvpairs[$odvan] = $odvav;
            }
            if ($odva['name'] == 'units') {
                $kvpairs[$odvan] = $odvav;
            }
            $odv_object[$vn] = $kvpairs;
        }
    }
    return $odv_object;
}

/**
 * adc_get_odv_object}
 */
/*
 * adcwps_query{
 */


function adcwps_query($protocol, $server, $service_path, $built_query) {
    $url = $protocol;
    $url .= '://';
    $url .= $server;
    $url .= '/';
    $url .= $service_path;
    $url .= '?';
    $url .= $built_query;
    $res = drupal_http_request($url);
    $data = hack_xml_namespace($res->data);
    $xmldata = new SimpleXMLElement($data);
    $jsondata = drupal_json_decode(drupal_json_encode($xmldata));
    return $jsondata;
}

/*
 * adcwps_query}
 */

/**
 * adc_get_datasets_fields{
 * !!!!!!!!!!!!!!!TODO: we need start row and end row for this search. effectively, "start row" and "start row + number of results per page global"
 * @param type $solr_server
 * @param type $solr_port
 * @param type $solr_core
 * @param type $metadata_identifier_array
 * @param type $fields_array
 * @return type
 */
function adc_get_datasets_fields($solr_server, $solr_port, $solr_core, $metadata_identifier_array, $fields_array, $start_row, $no_of_rows) {
    $fields = implode(',', $fields_array);
    $metadata_identifiers = implode('" OR "', $metadata_identifier_array);
    $metadata_identifiers = urlencode('("' . $metadata_identifiers . '")');
    /**
     * TODO 01
     * test{
     * sorting by time is not implemented yet
     * Not enough to do make change here. Needs a lot more work......
     */
//    if(defined(SORT_BY_TIME)){
//        $sort_param = '&sort='.METADATA_PREFIX.'temporal_extent_start_date+'.SORT_BY_TIME;
//        $url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_metadata_identifier:' . $metadata_identifiers . '&fl=' . $fields . $sort_param .'&start=' . $start_row . '&rows=' . $no_of_rows . '&wt=json';
//        sdpm($url);
//        }else{
//        $url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_metadata_identifier:' . $metadata_identifiers . '&fl=' . $fields . '&start=' . $start_row . '&rows=' . $no_of_rows . '&wt=json';        
//    }
    /**
     * test}
     */
    $url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_metadata_identifier:' . $metadata_identifiers . '&fl=' . $fields . '&start=' . $start_row . '&rows=' . $no_of_rows . '&wt=json';
    $result = drupal_http_request($url);
    $json = $result->data;

//sdpm($metadata_identifiers);
//    $metadata_identifiers = '("' . $metadata_identifiers . '")';
//sdpm($metadata_identifiers);
//$url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_metadata_identifier:"' . $metadata_identifier . '"&fl=' . $fields . '&wt=json&indent=true';
//    $url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_metadata_identifier:' . $metadata_identifiers . '&fl=' . $fields . '&start=' . $start_row . '&rows=' . $no_of_rows . '&wt=json';
//http://157.249.176.182:8080/solr/test-l1/select?q=id%3A%224307310f-6e94-582a-9d6e-60ae5907a78a%22&start=10&rows=20&wt=json&indent=true
//$url = 'http://157.249.176.182:8080/solr/test-l1/select?q=mmd_metadata_identifier%3A%225836f35d-ddda-5a2c-8277-2319d18ce825%22&fl=mmd_title%2Cmmd_metadata_identifier&wt=json&indent=true';
//sdpm(urldecode($url));
//sdpm("1- " . urldecode('http://157.249.176.182:8080/solr/test-l2/select?q=mmd_metadata_identifier%3A(%22bd4134ea-cc43-5584-96b4-7f43c7566047-c3%22+OR+%22f87ddd87-cef9-5a94-bb64-c28cfdf80bbe%22+OR+%225f9416de-b309-5593-afd2-6ad47221801f%22)&wt=json'));
    //sdpm("2- " . urldecode($url));
//sdpm($result);
    //sdpm(json_decode($json, TRUE));

    return json_decode($json, TRUE);
}

/**
 * adc_get_datasets_fields}
 */

/**
 * adc_get_datasets_fields_mikey{
 */
function adc_get_datasets_fields_mikey($solr_server, $solr_port, $solr_core, $metadata_identifier_array, $fields_array, $start_row, $no_of_rows) {
    $fields = implode(',', $fields_array);
    $metadata_identifiers = implode('" OR "', $metadata_identifier_array);
    $metadata_identifiers = urlencode('("' . $metadata_identifiers . '")');
    $url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_metadata_identifier:' . $metadata_identifiers . '&fl=' . $fields . '&start=' . $start_row . '&rows=' . $no_of_rows . '&wt=json';
    $result = drupal_http_request($url);
    $json = $result->data;
    return json_decode($json, TRUE);
}

/**
 * adc_get_datasets_fields_mikey}
 */
//
//function adc_get_dataset_fields_php($solr_server, $solr_port, $solr_core, $metadata_identifier_array, $fields_array) {
//    $fields = implode(',', $fields_array);
//    $metadata_identifiers = implode(' OR ', $metadata_identifier_array);
//    $metadata_identifiers = '(' . $metadata_identifiers . ')';
//    $url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_metadata_identifier:' . $metadata_identifiers . '&fl=' . $fields . '&wt=json';
//    $ch = curl_init($url);
////    curl_setopt($ch, CURLOPT_POST, 1);
////    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
////    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    $response = curl_exec($ch);
//    $json = json_decode($response, true);
//    curl_close($ch);
//    return $json;
////
////    $r = new HttpRequest($url, HttpRequest::METH_GET);
////    try {
////        $r->send();
////        if ($r->getResponseCode() == 200) {
////            $body = $r->getResponseBody();
////        }
////    }
////    catch (HttpException $ex) {
////        echo $ex;
////    }
////    return $body;
//}



/*
 * get all contributing institutions{
 */

function msb_get_institutions() {
    /**
     * not a mandatory metadata item
     */
    //$obj = msb_get_docs_fields(array(METADATA_PREFIX . 'personnel_organisation'));
    $obj = msb_get_docs_fields_cond(array(METADATA_PREFIX . 'personnel_organisation'),'mmd_collection:'.COLLECTIONS);
    $checkboxes = [];
    foreach ($obj['response']['docs'] as $doc) {
        if (count($doc) > 0) {
            if (!in_array($doc[METADATA_PREFIX . 'personnel_organisation'][0], $checkboxes)) {
                $checkboxes[] = $doc[METADATA_PREFIX . 'personnel_organisation'][0];
            }
        }
    }
    return $checkboxes;
}

/*
 * get all contributing intitutions}
 */


/*
 * get all contributing collections{
 */

function msb_facet_get_collections() {
    global $metsis_conf;
    if (isset($metsis_conf['collections'])) {
        $collections_to_display = array_map('trim', explode(',', $metsis_conf['collections']));
    }
    $checkboxes = [];
    $url = 'http://' . SOLR_SERVER_IP . ':' . SOLR_SERVER_PORT . '/solr/' . SOLR_CORE_PARENT . '/select?q=*:*&rows=1&wt=json&facet=true&facet.field=mmd_collection';
    $results = drupal_http_request($url);
    $json = json_decode($results->data);

    while ($current = current($json->facet_counts->facet_fields->mmd_collection)) {
        $cstring = (string) $current . '  (' . (string) next($json->facet_counts->facet_fields->mmd_collection) . ')';
        /**
         * test{
         */
        if (isset($collections_to_display)) {
            if (in_array($current, $collections_to_display)) {
                $checkboxes[] = $cstring;
            }
        }
        else {
            $checkboxes[] = $cstring;
        }
        /**
         * test}
         */
        //
        $current = next($json->facet_counts->facet_fields->mmd_collection);
    }
    return $checkboxes;
}

/*
 * get all contributing collections}
 */

/*
 * get chosen institutions{
 */

function msb_get_chosen_institutions($form_state) {
    $q = "";
    if (INSTITUTIONS_VISIBLE) {
        $chosen_institutions = array_filter($form_state['values']['institutions']['chosen_institutions']);
        if (!empty($chosen_institutions)) {
            $q = "mmd_personnel_organisation:(\"" . implode("\" OR \"", $chosen_institutions) . "\")";
        }
    }
    return $q;
}

/*
 * get chosen institutions}
 */


/*
 * get chosen collections{
 */

function msb_get_chosen_collections($form_state) {
    $q = "";
    if (COLLECTIONS_VISIBLE) {
        $chosen_collections = array_filter($form_state['values']['collections']['chosen_collections']);
        if (!empty($chosen_collections)) {
            $stripped_cc = [];
            foreach ($chosen_collections as $cc) {
                $stripped_cc[] = explode(' ', $cc)[0];
            };
            $q = "mmd_collection:(\"" . implode("\" OR \"", $stripped_cc) . "\")";
//            $q = "mmd_collection:(\"" . implode("\" OR \"", $chosen_collections) . "\")";
        }
    }
    return $q;
}

/*
 * get chosen institutions}
 */

/**
 * adc_get_link_list{
 */
function adc_get_link_list($href_array, $display_text_array) {
    $it = new MultipleIterator();
    $it->attachIterator(new ArrayIterator($href_array));
    $it->attachIterator(new ArrayIterator($display_text_array));

    $div = '<div id="adcllist">';
    $div .= '<ul>';
    foreach ($it as $a) {
        if (preg_match('/(<img).*/i', $a[1]) === 1) {
            //if $a[1] is an image tag, do not use the adc-button and adc-sbutton classes
            $div .= '<li><a href="' . $a[0] . '">' . $a[1] . '</a></li>';
        }
        else {
            $div .= '<li><a class="adc-button adc-sbutton" href="' . $a[0] . '">' . $a[1] . '</a></li>';
        }
    }
    $div .= '</ul>';
    $div .= '</div>';

    return $div;
}

/**
 * adc_get_link_list}
 */

/**
 * adc_get_rir_dar_kv{
 * $metadata_identifier is: 
 *  Solr mmd_metadata_identifier
 * $solr_array is one of:
 *  Solr mmd_data_access_resource array
 *  Solr mmd_related_information_resource
 */
function adc_get_rir_dar_kv($metadata_identifier, $solr_array) {
    $this_kv = [];
    $this_kv['metadata_identifier'] = $metadata_identifier;
    foreach ($solr_array as $sa) {
        $la = explode(':', $sa, 2);
        $protocol = str_replace('"', '', $la[0]);
        $ma = explode(',', $la[1], 2);
        $uri = str_replace('"', '', $ma[0]);
        $na = explode(':', $ma[1], 2);
        $description_name = str_replace('"', '', $na[0]);
        $description_value = str_replace('"', '', $na[1]);
        $this_kv[$protocol]['uri'] = $uri;
        $this_kv[$protocol][$description_name] = $description_value;
    }
    return($this_kv);
}

/**
 * adc_get_rir_dar_kv}
 */
/*
 * adc_get_md_kv{
 */


/**
 * junk{
 */

/**
 * junk}
 */
function adc_get_md_kv($solr_core_level, $dataset_id) {
    global $base_url;
    $md_kv = [];
    if (defined('SOLR_METADATA_BUTTON_TEXT')) {
        $solr_metadata_button_text = SOLR_METADATA_BUTTON_TEXT;
    }
    else {
        $solr_metadata_button_text = "Metadata";
    }
    $md_kv['href'] = $base_url . '/metsis/display/metadata/?core=' . $solr_core_level . '&datasetID=' . $dataset_id;
    $md_kv['display_text'] = $solr_metadata_button_text;
    return $md_kv;
}

/*
 * adc_get_md_kv}
 */
/*
 * msb_get_md_links{
 */

function msb_get_md_links($solr_core_level, $dataset_id) {
    global $base_url;
    if (defined('SOLR_METADATA_BUTTON_TEXT')) {
        $solr_metadata_button_text = SOLR_METADATA_BUTTON_TEXT;
    }
    else {
        $solr_metadata_button_text = "Metadata";
    }
    $url = ''
        . '<div class="botton-wrap">'
        . '<a class="adc-button adc-sbutton ext_data_source" href="' . $base_url . '/metsis/display/metadata/?core=' . $solr_core_level . '&datasetID=' . $dataset_id . '" >' . $solr_metadata_button_text . '</a>'
        . '</div>';
    return $url;
}

/*
 * msb_get_md_links}
 */

/**
 * adc_get_title_kv{
 */
function adc_get_title_kv($solr_obj, $metadata_identifier) {
//    sdpm($solr_obj);
//    sdpm($metadata_identifier);
//tries to link the title to one of:
// mmd_related_information_resource: "Dataset landing page"
// mmd_data_access_resource: "HTTP"

    $title_kv = [];
    $title_kv['metadata_identifier'] = $metadata_identifier;
    foreach ($solr_obj['response']['docs'] as $so) {
        if ($so['mmd_metadata_identifier'] == $metadata_identifier) {
            /**
             * test{
             */
//            if(!isset($so['mmd_title'])){
//                sdpm($metadata_identifier);
//            }
            /**
             * test}
             */
            //if (count($so['mmd_title']) > 0) {
            $title_kv['title'] = $so['mmd_title'][0];
            //}
            //else {
            //    $title_kv['title'] = "";
            //}
            if (isset($so['mmd_related_information_resource'])) {
                $rir_kv = adc_get_rir_dar_kv($metadata_identifier, $so['mmd_related_information_resource']);
                $title_kv['href'] = $rir_kv['Dataset landing page']['uri'];
            }
            elseif (isset($so['mmd_data_access_resource'])) {
                $dar_kv = adc_get_rir_dar_kv($metadata_identifier, $so['mmd_data_access_resource']);
                $title_kv['href'] = $dar_kv['HTTP']['uri'];
            }
            else {
                $title_kv['href'] = "";
            }
        }
    }
    return($title_kv);
}

/**
 * adc_get_title_kv}
 */

/**
 * adc_get_thumbnail_kv{
 */
function adc_get_thumbnail_kv($solr_obj, $metadata_identifier) {
    //needs to check what the thumbnail href should be. Is it a WMS map display or
    //time series etc. Check for feature type in the solr_obj. In other words
    //the function adc_get_datasets_fields should have added feature type, if it is set to 
    //the solr_obj
    //https://xyz.metsis.met.no/metsis/map/wms?dataset=26e2956c-368f-11e7-ba85-c8d3ff6f63a2
    $thumbnail_kv = [];
    $thumbnail_kv['metadata_identifier'] = $metadata_identifier;
    foreach ($solr_obj['response']['docs'] as $so) {
        if ($so['mmd_metadata_identifier'] == $metadata_identifier) {
            if (isset($so['feature_type'])) {
                $thumbnail_kv['feature_type'] = $so['feature_type'];
            }
            $thumbnail_kv['thumbnail_data'] = $so['thumbnail_data'];
            $thumbnail_kv['display_text'] = '<img src="' . $so['thumbnail_data'] . '"';
        }
    }
    return($thumbnail_kv);
}

/**
 * adc_get_thumbnail_kv}
 */
/*
 * get docs fields
 * metsis lib function
 * query SOLR on  field(s)
 */

function msb_get_docs_fields($fields) {
    $fl = implode(",", $fields);
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => "*:*",
      "rows" => SEARCH_MAXIMUM_ROWS_TO_FETCH,
      "wt" => "json",
      "fl" => "$fl",
        )
    );
    return json_decode($res['body'], true);
}

/**
 * get docs fields_cond}
 */
/*
 */

function msb_get_docs_fields_cond($fields,$condition) {
    $fl = implode(",", $fields);
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => $condition,
      "rows" => SEARCH_MAXIMUM_ROWS_TO_FETCH,
      "wt" => "json",
      "fl" => "$fl",
        )
    );
    return json_decode($res['body'], true);
}

/**
 * get docs fields}
 */




/*
 * getCapDoc{
 */

function getCapDoc() {
    $query = drupal_get_query_parameters();
    if (count($query) > 0) {
        $url = $query['dataset'] . '&REQUEST=' . $query['REQUEST'];
        print drupal_http_request($url)->data;
    }
}

/*
 * getCapDoc}
 */

/*
 * adc_array_to_solror{
 */

function adc_array_to_solror($trimmed_array) {
    $qs = '("';
    $qs .= implode('" OR "', $trimmed_array);
    $qs .= '")';
    return $qs;
}

/*
 * adc_array_to_solror}
 */

/**
 * adc_get_solr_core{
 */
function adc_get_solr_core($metadata_identifier_array) {
    $data = [];

    $found_in_parent_core = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_PARENT, $metadata_identifier_array, array(METADATA_PREFIX . 'metadata_identifier'), 0, 1000000);
    $found_in_child_core = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_CHILD, $metadata_identifier_array, array(METADATA_PREFIX . 'metadata_identifier'), 0, 1000000);

    foreach ($metadata_identifier_array as $mi) {
        if (isset($found_in_parent_core['response']['docs'])) {
            foreach ($found_in_parent_core['response']['docs'] as $doc) {
                if ($doc[METADATA_PREFIX . 'metadata_identifier'] == $mi) {
                    $data[$mi] = SOLR_CORE_PARENT;
                }
            }
        }
        if (isset($found_in_child_core['response']['docs'])) {
            foreach ($found_in_child_core['response']['docs'] as $doc) {
                if ($doc[METADATA_PREFIX . 'metadata_identifier'] == $mi) {
                    $data[$mi] = SOLR_CORE_CHILD;
                }
            }
        }
    }
    return $data;
}

/**
 * adc_get_solr_core}
 */

/**
 * adc_has_related_dataset{
 */
function adc_has_related_dataset($solr_core, $metadata_identifier_array) {
    //NOTE This can only be applied to level one since level two also has related_dataset
    //applying this blindly will exclude all leve 2 also !!!
    $has_related_dataset = [];
    $rd_check = adc_get_datasets_fields(
        SOLR_SERVER_IP, SOLR_SERVER_PORT, $solr_core, $metadata_identifier_array, array(METADATA_PREFIX . 'metadata_identifier',
      METADATA_PREFIX . 'related_dataset',
      METADATA_PREFIX . 'title'), 0, 1000000
    );
    foreach ($rd_check['response']['docs'] as $doc) {
        if (isset($doc[METADATA_PREFIX . 'related_dataset'])) {
            if (count($doc[METADATA_PREFIX . 'related_dataset']) > 0) {
                $has_related_dataset[$doc[METADATA_PREFIX . 'metadata_identifier']] = $doc[METADATA_PREFIX . 'title'];
            }
        }
    }
    return $has_related_dataset;
}

/**
 * adc_has_related_dataset}
 */
/*
 * msb_concat_data_access_resource{
 */

function msb_concat_data_access_resource($data_access_resource_array) {
    $gar = array();
    foreach ($data_access_resource_array as $dara) {

        /*
         * test{
         */
        $dara = explode(":", $dara, 2);
        if (!isset($dara[1])) {
            $dara[1] = "--";
            $dara[0] = "--";
        }
        else {
            $eara = explode(",", $dara[1]);
        }
        if (!isset($eara[1])) {
            $eara[1] = "--";
            $eara[0] = "--";
        }
        else {
            $fara = explode(":", $eara[1]);
        }
        if (!isset($fara[0])) {
            $fara[0] = "--";
            $fara[1] = "--";
        }

        $patterns = array();
        $patterns[0] = '/(\s)/';
        $patterns[1] = '/(\")/';
        $replacements = array();
        $replacements[0] = '_';
        $replacements[1] = '';
        $dara[0] = preg_replace($patterns, $replacements, $dara[0]);
        $dara[1] = preg_replace($patterns[1], $replacements[1], $dara[1]);
        $fara[0] = preg_replace($patterns, $replacements, $fara[0]);
        $fara[1] = preg_replace($patterns[1], $replacements[1], $fara[1]);
        $eara[0] = preg_replace($patterns, $replacements, $eara[0]);
        $eara[1] = preg_replace($patterns[1], $replacements[1], $eara[1]);
        /**
         * hack - a few datasets return "URL" on querying  mmd_data_access_resource for OPeNDAP!
         * This bad metadata. Data providers MUST provide a valid mmd_data_access_resource for each
         * type defined under  mmd_data_access_type
         * 
         */
        if ($dara[1] == "URL") {
            //$dara[1] = $dara[0] . " access unavailable";
            $dara[1] = MISSING_METADATA;
        }
        $gar[$dara[0]]['url'] = $eara[0];
        $gar[$dara[0]][$fara[0]] = $fara[1];
    }

    return $gar;
}

/*
 * msb_concat_data_access_resource}
 */

/**
 * test00{
 */
function test00() {
//    sdpm('-----------------------Running test00-----------------------');
//    $zz_solr_array = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, 'nbs-l1', array('S2A_MSIL1C_20170627T144751_N0205_R082_T31XFL_20170627T144749'), array('mmd_related_information_resource', 'mmd_data_access_resource'), 0, 100);
//    sdpm(adc_get_rir_dar_kv('S2A_MSIL1C_20170627T144751_N0205_R082_T31XFL_20170627T144749', $zz_solr_array['response']['docs'][0]['mmd_related_information_resource']));
//    sdpm(adc_get_rir_dar_kv('S2A_MSIL1C_20170627T144751_N0205_R082_T31XFL_20170627T144749', $zz_solr_array['response']['docs'][0]['mmd_data_access_resource']));
//    sdpm(adc_get_md_kv('l1', 'S3A_OL_1_EFR____20180213T101722_20180213T102022_20180214T135646_0179_028_008_1980_LN1_O_NT_002'));
}

/**
         * test00}
         */
        
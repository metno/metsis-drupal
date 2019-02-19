<?php

// this has to be made more generic
// to allow fetching other properties
// 
function get_proj4_strings($name = "all") {
    $epsg = array(
      "EPSG:3031" => array(
        "area" => "Antarctica",
        "proj4string" => "+proj=stere +lat_0=-90 +lat_ts=-71 +lon_0=0 +k=1 +x_0=0 +y_0=0 +datum=WGS84 +units=m +no_defs",
        "description" => "WGS 84 / Antarctic Polar Stereographic",
      ),
      "EPSG:32633" => array(
        "area" => "World - N hemisphere - 12°E to 18°E - by country",
        "proj4string" => "+proj=utm +zone=33 +datum=WGS84 +units=m +no_defs",
        "description" => "WGS 84 / UTM zone 33N",
      ),
      "EPSG:32661" => array(
        "area" => "World - north of 60°N",
        "proj4string" => "+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs",
        "description" => "WGS 84 / UPS North",
      ),
      "EPSG:32761" => array(
        "area" => "World - south of 60°S",
        "proj4string" => "+proj=stere +lat_0=-90 +lat_ts=-90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs",
        "description" => "WGS 84 / UPS South",
      ),
      "EPSG:3411" => array(
        "area" => "World - north of 30°N",
        "proj4string" => "+proj=stere +lat_0=90 +lat_ts=70 +lon_0=-45 +k=1 +x_0=0 +y_0=0 +a=6378273 +b=6356889.449 +units=m +no_defs",
        "description" => "NSIDC Sea Ice Polar Stereographic North",
      ),
      "EPSG:3412" => array(
        "area" => "World - south of 40°S",
        "proj4string" => "+proj=stere +lat_0=-90 +lat_ts=-70 +lon_0=0 +k=1 +x_0=0 +y_0=0 +a=6378273 +b=6356889.449 +units=m +no_defs",
        "description" => "NSIDC Sea Ice Polar Stereographic South",
      ),
      "EPSG:3413" => array(
        "area" => "World - north of 30°N",
        "proj4string" => "+proj=stere +lat_0=90 +lat_ts=70 +lon_0=-45 +k=1 +x_0=0 +y_0=0 +datum=WGS84 +units=m +no_defs",
        "description" => "WGS 84 / NSIDC Sea Ice Polar Stereographic North",
      ),
      "EPSG:3995" => array(
        "area" => "World - north of 60°N",
        "proj4string" => "+proj=stere +lat_0=90 +lat_ts=71 +lon_0=0 +k=1 +x_0=0 +y_0=0 +datum=WGS84 +units=m +no_defs",
        "description" => "WGS 84 / Arctic Polar Stereographic",
      ),
      "EPSG:4326" => array(
        "area" => "World",
        "proj4string" => "+proj=longlat +datum=WGS84 +no_defs",
        "description" => "WGS 84",
      ),
      "EPSG:3575" => array(
        "area" => "World - north of 45°N",
        "proj4string" => "+proj=laea +lat_0=90 +lon_0=10 +x_0=0 +y_0=0 +ellps=WGS84 +datum=WGS84 +units=m +no_defs",
        "description" => "WGS 84 / North Pole LAEA Europe",
      ),
      "EPSG:25833" => array(
        "area" => "Europe - 12°E to 18°E and ETRS89 by country",
        "proj4string" => "+proj=utm +zone=33 +ellps=GRS80 +units=m +no_defs",
        "description" => "ETRS89 / UTM zone 33N",
      ),
    );
    if ($name == "all") {
        return $epsg;
    }
    else {
        return $epsg[$name]['proj4string'];
    }
}

/*
 * This is what is used in Metamod v2.12
  common/lib/MetNo/Fimex.pm:    'EPSG:32661' => '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs',
  common/lib/MetNo/Fimex.pm:    'EPSG:32761' => '+proj=stere +lat_0=-90 +lat_ts=-90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs',
  common/lib/MetNo/Fimex.pm:    'EPSG:3411'  => '+proj=stere +lat_0=90 +lat_ts=70 +lon_0=-45 +k=1 +x_0=0 +y_0=0 +a=6378273 +b=6356889.449 +units=m +no_defs',
  common/lib/MetNo/Fimex.pm:    'EPSG:3412'  => '+proj=stere +lat_0=-90 +lat_ts=-70 +lon_0=0 +k=1 +x_0=0 +y_0=0 +a=6378273 +b=6356889.449 +units=m +no_defs',
  common/lib/MetNo/Fimex.pm:    'EPSG:3413'  => '+proj=stere +lat_0=90 +lat_ts=70 +lon_0=-45 +k=1 +x_0=0 +y_0=0 +datum=WGS84 +units=m +no_defs',
  common/lib/MetNo/Fimex.pm:    'EPSG:3995'  => '+proj=stere +lat_0=90 +lat_ts=71 +lon_0=0 +k=1 +x_0=0 +y_0=0 +datum=WGS84 +units=m +no_defs',
  common/lib/MetNo/Fimex.pm:    'EPSG:32633' => '+proj=utm +zone=33 +datum=WGS84 +units=m +no_defs',
  common/lib/MetNo/Fimex.pm:    'EPSG:4326'  => '+proj=longlat +datum=WGS84 +no_defs',
 * 
 */
/*
 * adc_get_od_proj4($od_data){
 * TODO: move to metsis_lib
 */

function adc_get_od_proj4($od_data) {
    //TODO. We fetch the the proj4 string from opendap stream to provide the
    //      default projection when given one agragated dataset, 
    //      but we need to handle the case where there
    //      are more than one datasets
    //   
    // NOTE: each project needs to be handled exceptionally since there seems 
    // to be no standard for if and where this element is present
    $od_proj4 = array();
    if (isset($od_data['Attribute']['Attribute'])) {

        foreach ($od_data['Attribute']['Attribute'] as $k => $v) {
            if ($od_data['Attribute']['Attribute'][$k]['@attributes']['name'] == "area") {
                $od_proj4['Original'] = array(
                  "area" => $od_data['Attribute']['Attribute'][$k]['value'],
                  "description" => $od_data['Attribute']['Attribute'][$k]['value'],
                );
            }
        }
    }
    if (isset($od_data['Int32'])) {
        foreach ($od_data['Int32']['Attribute'] as $k => $v) {

            if ($od_data['Int32']['Attribute'][$k]['@attributes']['name'] == "proj4_string" || $od_data['Int32']['Attribute'][$k]['@attributes']['name'] == "proj4") {
                $od_proj4["Original"]["proj4string"] = $od_data['Int32']['Attribute'][$k]['value'];
            }
        }
    }
    elseif (isset($od_data['String']['Attribute'])) {
        foreach ($od_data['String']['Attribute'] as $k => $v) {
            if ($od_data['String']['Attribute'][$k]['@attributes']['name'] == "proj4_string" || $od_data['String']['Attribute'][$k]['@attributes']['name'] == "proj4") {
                $od_proj4["Original"]["proj4string"] = $od_data['String']['Attribute'][$k]['value'];
            }
        }
    }
    else {
        $od_proj4["Original"]["proj4string"] = "";
    }
    return $od_proj4;
}

/*
 * adc_get_od_proj4($od_data)}
 */

/**
 * deprecated. to be replaced with request to OPeNDAP parser service
 * adc_get_od_data($opendap_ddx){
 */
function adc_get_od_data($opendap_ddx) {

    $ddx = drupal_http_request($opendap_ddx);
    if ($ddx->code == '200') {
        $od_data = new SimpleXMLElement($ddx->data);
        return drupal_json_decode(drupal_json_encode($od_data));
    }
    else {
        //todo refactor
        drupal_set_message("Failed to fetch (OPeNDAP) data from data server", 'error');
        drupal_goto('metadata_search');
    }
    return;
}

/*
 * adc_get_od_data}
 */

/**
 * todo 2 
 * need a swtich statement to work through all elements only once.
 * adc_get_od_temporal_extent($jod_data){
 * TODO: move to metsis_lib
 */
function adc_get_od_temporal_extent($jod_data) {
    $temporal_extent = [];
    if (isset($jod_data['Attribute']['Attribute'])) {

        foreach ($jod_data['Attribute']['Attribute'] as $k => $v) {
            if (($jod_data['Attribute']['Attribute'][$k]['@attributes']['name'] == "start_date") || ($jod_data['Attribute']['Attribute'][$k]['@attributes']['name'] == "start_time") || ($jod_data['Attribute']['Attribute'][$k]['@attributes']['name'] == "time_coverage_start")) {
                $temporal_extent["start_date"] = $jod_data['Attribute']['Attribute'][$k]['value'];
            }
            else {
                $temporal_extent["start_date"] = "";
            }
            if (($jod_data['Attribute']['Attribute'][$k]['@attributes']['name'] == "stop_date") || ($jod_data['Attribute']['Attribute'][$k]['@attributes']['name'] == "stop_time") || ($jod_data['Attribute']['Attribute'][$k]['@attributes']['name'] == "time_coverage_end")) {
                $temporal_extent["stop_date"] = $jod_data['Attribute']['Attribute'][$k]['value'];
            }
            else {
                $temporal_extent["stop_date"] = "";
            }
        }
    }
    else {


        if (defined('SITE_STATUS')) {
            if (SITE_STATUS === 'dev' || SITE_STATUS === 'test') {
                drupal_set_message("Could not find all \"temporal extent\" data", 'warning');
            }
        }
    }
    return $temporal_extent;
}

/*
 * adc_get_od_temporal_extent}
 */



/*
 * adc_set_message($receipt){
 */

function adc_set_message($receipt) {
    $message = "";
    $message .= "Thank you for requesting data from ";
    $message .= "!site";
    $message .= ".";
    $message .= " ";
    $message .= "An e-mail will be sent to";
    $message .= " ";
    $message .= "!email";
    $message .= " ";
    $message .= "when your order is ready";
    $message .= ".";
    drupal_set_message(t($message, array(
      '!site' => '<strong>' . $receipt['site'] . '</strong>',
      '!email' => '<strong>' . $receipt['email'] . '</strong>',
            )
        ), 'status', FALSE);
}

/*
 * adc_set_message}
 */

/*
 * adc_trim_string($string, $remove){
 */

function adc_trim_string($string, $remove) {
    //this is to remove the "UTC" and the like substrings from 
    //time and date strings in the metadata or OPeNDAP strings.
    //it is a stopgap solution and should be removed when better 
    //handling of these strings is in place in the basket service
    //where this belongs!
    $patterns = array();
    $patterns[0] = '/(' . $remove . ')/';
    $replacements = array();
    $replacements[0] = '';
    return preg_replace($patterns, $replacements, $string);
}

/*
 * adc_trim_string}
 */

/**
 * todo 2
 * refactor
 * NOT in use yet
 * OPeNDAP is not easy to search with this since there is no standard 
 * hierachical relationship between key and value!!!
 * @param array $array
 * @param type $needle
 * @return type
 */
function adc_recursive_array_search(array $array, $key) {
    $iterator = new RecursiveArrayIterator($array);
    $recursive = new RecursiveIteratorIterator(
        $iterator, RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($recursive as $k => $v) {
        if ($k === $key) {
            return $v;
        }
    }
}

/**
 * NOT in use yet. Need to return key=>value pairs
 * XML to JSON conversion without '@attributes'
 */
function XML2JSON($xml) {

    function normalizeSimpleXML($obj, &$result) {
        $data = $obj;
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $res = null;
                normalizeSimpleXML($value, $res);
                if (($key == '@attributes') && ($key)) {
                    $result = $res;
                }
                else {
                    $result[$key] = $res;
                }
            }
        }
        else {
            $result = $data;
        }
    }

    normalizeSimpleXML(simplexml_load_string($xml), $result);
    return json_encode($result);
}

/**
 * NOT in use yet
 * very expensive function.
 * adc_get_array_key_path($array, $key){
 */
function adc_get_array_key_path($array, $key) {

    if (array_key_exists($key, $array)) {
        return array($key);
    }
    else {
        foreach ($array as $key => $subarr) {
            if (is_array($subarr)) {
                $ret = adc_get_array_key_path($subarr, $key);

                if ($ret) {
                    $ret[] = $key;
                    return $ret;
                }
            }
        }
    }

    return null;
}

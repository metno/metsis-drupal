<?php

include_once drupal_get_path('module', 'metsis_lib') . '/includes/metsis_lib.constants.inc';

function adc_require_login($calling_uri, $message) {
    global $user;
    if (!user_is_logged_in()) {
        drupal_set_message("<b>" . t($message) . "</b>", 'warning');
        $dest = drupal_get_destination();
        drupal_goto('user/login', array('query' => $calling_uri));
    }
}

function in_array_r($needle, $haystack, $strict = true) {
    foreach ($haystack as $value) {
        if (( $strict ? $value === $needle : $value == $needle ) || ( is_array($value) && in_array_r($needle, $value, $strict))) {
            return $needle;
        }
    } return false;
}

function array_search_recursive($needle, $haystack, $strict = false, $path = array()) {
    if (!is_array($haystack)) {
        return false;
    } foreach ($haystack as $key => $val) {
        if (is_array($val) && $subPath = array_search_recursive($needle, $val, $strict, $path)) {
            $path = array_merge($path, array($key), $subPath);
            return $path;
        }
        elseif ((!$strict && $val == $needle) || ($strict && $val === $needle)) {
            $path[] = $key;
            return $path;
        }
    } return false;
}

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
    } $fully_qualified_value = '$json' . $fully_qualified_key . "['value']";
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
            $records[$pk][$f] = $result->$f;
        }
    } return $records;
}

function get_metsis_date($date_string, $format) {
    $d = new DateTime($date_string);
    return $d->format($format);
}

function adc_get_wms_get_capabilities($uri) {
    $result = drupal_http_request($uri);
    $data = $result->data;
    $xml = simplexml_load_string($data);
    $json = json_encode($xml);
    $array = json_decode($json, TRUE);
    return json_decode($json, TRUE);
}

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
    } return false;
}

function adc_get_metadata_item($metadata_identifier, $metadata_item) {
    global $metsis_conf;
    $con = new HttpConnection($metsis_conf['solr_server_ip'], $metsis_conf['solr_server_port']);
    $res = $con->get('/solr/' . $metsis_conf['solr_core_parent'] . '/select', array("q" => $metsis_conf['metadata_prefix'] . "metadata_identifier:" . '"' . $metadata_identifier . '"', "fl" => $metadata_item, "wt" => "json", "indent" => "true"));
    $body = json_decode($res['body'], true);
    return $body['response']['docs'];
}

function adc_get_data_access_resource($metadata_identifier) {
    global $metsis_conf;
    $raw = adc_get_metadata_item($metadata_identifier, $metsis_conf['metadata_prefix'] . "data_access_resource");
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
    } return($my_data_access_resource);
}

function adc_has_feature_type($metadata_identifier, $ft) {
    $feature_type = adc_get_feature_type($metadata_identifier);
    if ($feature_type === $ft) {
        return 1;
    } return 0;
}

function adc_get_feature_type($metadata_identifier) {
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_CONFIG . '/select', array("q" => METADATA_PREFIX . "metadata_identifier:" . '"' . $metadata_identifier . '"', "fl" => "feature_type", "wt" => "json", "indent" => "true"));
    $body = json_decode($res['body'], true);
    if ($body['response']['numFound'] > 0) {
        if (isset($body['response']['docs'][0]['feature_type'])) {
            return $body['response']['docs'][0]['feature_type'];
        }
    } return 0;
}

function adc_get_collection($collection) {
    $words = explode(",", $collection);
    array_walk($words, 'trimV');
    array_walk($words, 'dquoteV');
    $q = METADATA_PREFIX . "collection:(" . implode(" OR ", $words) . ")";
    return $q;
}

function trimV(&$v) {
    $v = trim($v);
}

function dquoteV(&$v) {
    $v = '"' . $v . '"';
}

function deprecated_adc_get_od_variables($od_data_object) {
    $od_vars = array();
    $counter = 0;
    foreach ($od_data_object['Grid'] as $dg) {
        if (isset($dg['@attributes']['name'])) {
            $od_vars[$counter]['name'] = $dg['@attributes']['name'];
        }
        else {
            $od_vars[$counter]['name'] = "";
        } foreach ($dg['Attribute'] as $dga) {
            $od_vars[$counter][$dga['@attributes']['name']] = isset($dga['value']) ? $dga['value'] : "";
        } $counter = $counter + 1;
    } return $od_vars;
}

function adc_parse_related_information_resource($rir) {
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
    } return $related_information_resource;
}

function adc_get_button_markup($metadata_identifier, $button_uri) {
    $url = '' . '<div class="botton-wrap">' . '<a class="adc-button adc-sbutton" href="' . $button_uri . '">' . $metadata_identifier . '</a>' . '</div>';
    return $url;
}

function adc_get_metadata() {
    global $metsis_conf;
    $metadata_sort_order = $metsis_conf['metadata_sort_order'];
    $page_inputs = drupal_get_query_parameters();
    $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
    $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => METADATA_PREFIX . "metadata_identifier:" . '"' . $page_inputs['datasetID'] . '"', "wt" => "json", "indent" => "true"));
    $body = json_decode($res['body'], true);
    if (array_filter($body['response']['docs'][0])) {
        $metadata = array_filter($body['response']['docs'][0]);
        if (isset($metadata[METADATA_PREFIX . 'cloud_cover_value'])) {
            $metadata[METADATA_PREFIX . 'cloud_cover_value'] = number_format($metadata[METADATA_PREFIX . 'cloud_cover_value'], $metsis_conf['results_number_decimal_display_format']);
        } $zmetadata = array_intersect_key($metadata, array_flip($metsis_conf['metadata_visible']));
        $metadata = $zmetadata;
        $header = array('Metadata key', 'Metadata value');
        $top_array = array();
        $bottom_array = array();
        foreach ($metadata as $k => $v) {
            if (in_array($k, $metadata_sort_order)) {
                $top_array[$k] = $v;
            }
            else {
                $bottom_array[$k] = $v;
            }
        } $sorted_top_array = array();
        foreach (array_values($metadata_sort_order) as $key) {
            if (key_exists($key, $top_array)) {
                $sorted_top_array[$key] = $top_array[$key];
            }
        } ksort($bottom_array);
        $top_rows = h_adc_get_metadata($sorted_top_array);
        $bottom_rows = h_adc_get_metadata($bottom_array);
        $rows = array_merge($top_rows, $bottom_rows);
        $metadata_table = theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('class' => array('ext_data_souce'),),));
        return theme('metadata', array('metadata_table' => $metadata_table));
    }
}

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
                    default: $url_ext = "";
                        break;
                } $value[$k] = $protocol . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
            }
        } if ($key == METADATA_PREFIX . "related_information_resource") {
            foreach ($value as $k => $v) {
                $la = explode(":", $v, 2);
                $ma = explode(",", $la[1]);
                $na = explode(":", $ma[1]);
                $protocol = str_replace('"', '', $la[0]);
                switch ($protocol) {
                    default: $url_ext = "";
                        break;
                } $value[$k] = $protocol . ': <a class="' . $key . '" href="' . str_replace('"', '', $ma[0]) . $url_ext . '">' . $ma[0] . '</a>';
            }
        } if ($key == METADATA_PREFIX . "data_center_data_center_url") {
            $value = '<a class="' . $key . '" href=' . $value . '>' . $value . '</a>';
        } if ($key == METADATA_PREFIX . "data_center_contact_email") {
            $value = '<a class="' . $key . '" href=mailto:' . $value . '>' . $value . '</a>';
        } if ($key == METADATA_PREFIX . "personnel_email") {
            $value = '<a class="' . $key . '" href=mailto:' . $value[0] . '>' . $value[0] . '</a>';
        } if (is_array($value)) {
            $value = implode("<br>", $value);
        } $pattern[0] = "/" . METADATA_PREFIX . "/";
        $pattern[1] = "/_/";
        $replacement[0] = "";
        $replacement[1] = " ";
        $key = preg_replace($pattern, $replacement, $key);
        $row = array(strtoupper($key), $value);
        $rows[] = $row;
    } return $rows;
}

function h_adc_has_data_access_resource($solr_doc) {
    if (isset($solr_doc[METADATA_PREFIX . 'data_access_resource'])) {
        return 1;
    }
    else {
        return 0;
    }
}

function object2array($object) {
    return @json_decode(@json_encode($object), 1);
}

function hack_xml_namespace($xml) {
    return preg_replace('~(</?|\s)([a-z0-9_]+):~is', '$1$2_', $xml);
}

function adc_get_wps_array($wps_xml) {
    return object2array(simplexml_load_string(hack_xml_namespace($wps_xml)));
}

function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0C2f) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0x2Aff), mt_rand(0, 0xffD3), mt_rand(0, 0xff4B));
}

function adc_get_random_file_name() {
    $fn = "" . time() . "-" . generate_uuid() . "";
    return $fn;
}

function adc_get_od_global_attributes($metadata_identifier, $collection_core) {
    $odquery = '{
                findAllAttributes(
                  datasetId: "' . $metadata_identifier . '", collection: "' . $collection_core . '"
                    ) {
                        name value
                        
                    }
               }';
    $con = new HttpConnection(METSIS_OPENDAP_PARSER_IP, METSIS_OPENDAP_PARSER_PORT);
    $res = $con->get(METSIS_OPENDAP_PARSER_SERVICE, array("query" => $odquery,));
    $jres = json_decode($res['body'], true);
    return $jres;
}

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
    $res = $con->get(METSIS_OPENDAP_PARSER_SERVICE, array("query" => $odquery,));
    $jres = json_decode($res['body'], true);
    return $jres;
}

function adc_get_od_ga($od_global_attributes_array, $attribute_name) {
    $this_odga = array();
    foreach ($od_global_attributes_array as $odgaa) {
        if ($odgaa['name'] == $attribute_name) {
            $this_odga[trim($odgaa['name'])] = $odgaa['value'];
        }
    } return $this_odga;
}

function adc_get_odv_standard_names($odv_object, $exclude_vars_array) {
    $odv_standard_names = [];
    foreach ($odv_object as $odvo) {
        if (key_exists('standard_name', $odvo)) {
            if (in_array(trim($odvo['standard_name']), $exclude_vars_array)) {
                continue;
            } $odv_standard_names[$odvo['standard_name']] = $odvo['standard_name'];
        }
    } return $odv_standard_names;
}

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
            } if ($odva['name'] == 'standard_name') {
                $kvpairs[$odvan] = $odvav;
            } if ($odva['name'] == 'units') {
                $kvpairs[$odvan] = $odvav;
            } $odv_object[$vn] = $kvpairs;
        }
    } return $odv_object;
}

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

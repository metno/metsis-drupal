<?php

use \Drupal\metsis_lib\HttpConnection;
use \Drupal\metsis_lib\HeaderList;
use \Drupal\metsis_lib\XmlElement;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Core\Render\Markup;
//use Drupal\filter\Render\FilteredMarkup;
use Symfony\Component\HttpFoundation\RedirectResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;


/**
 * Add constants for module to work without metsis_qsearch
  */
global $metsis_conf;
//define('SOLR_SERVER_IP', $metsis_conf['solr_server_ip']);
//define('SOLR_SERVER_PORT', $metsis_conf['solr_server_port']);

//Include the constants file
include_once drupal_get_path('module', 'metsis_lib') . '/includes/metsis_lib.constants.inc';
//include_once drupal_get_path('module', 'metsis_qsearch') . '/metsis_qsearch.constants.inc';

function adc_require_login($calling_uri, $message) {
  $user = \Drupal::currentUser();
  if (!\Drupal::currentUser()->isAuthenticated()) {
    \Drupal::messenger()->addWarning("<b>" . t($message) . "</b>");
    $dest = drupal_get_destination();
    //drupal_goto('user/login', array('query' => $calling_uri));
    return new RedirectResponse(\Drupal\Core\Url::fromRoute('user.login'));
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
  $json = \Drupal\Component\Serialization\Json::decode(\Drupal\Component\Serialization\Json::encode($xml));
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
    $query = \Drupal::database()->select($table, 't');
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
  $client = \Drupal::httpClient();
    $request = $client->createRequest(array('GET', $uri));
  try {
    $response = $client->get($uri);
    $data = $response->getBody();
  }
  catch (RequestException $e) {
    watchdog_exception('metsis_lib', $e);
  }
  $data = $request->ge;
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

function adc_get_metadata_item($solr_core, $metadata_identifier, $metadata_item) {
  global $metsis_conf;
  $con = new \Drupal\metsis_lib\HttpConnection($metsis_conf['solr_server_ip'], $metsis_conf['solr_server_port']);
  $res = $con->get('/solr/' . $solr_core . '/select', array("q" => $metsis_conf['metadata_prefix'] . "metadata_identifier:" . '"' . $metadata_identifier . '"', "fl" => $metadata_item, "wt" => "json", "indent" => "true"));
  $body = json_decode($res['body'], true);
  return $body['response']['docs'];
}

function adc_get_data_access_resource($solr_core, $metadata_identifier) {
  global $metsis_conf;
  $raw = adc_get_metadata_item($solr_core, $metadata_identifier, $metsis_conf['metadata_prefix'] . "data_access_resource");
  $my_data_access_resource = [];
  foreach ($raw as $key => $value) {
    foreach ($value as $k => $v) {
      foreach ($v as $kk => $vv) {
        $la = explode(':', $vv, 2);
        $protocol = str_replace('"', '', $la[0]);
        $ma = explode(',', $la[1], 2);
        $uri = str_replace('"', '', $ma[0]);
        // Test to aviod array offset waring
        if( isset($ma[1])) {
          $na = explode(':', $ma[1], 2);
        }
        else {
          $na = explode(':', $ma[0], 2);
        }
        //var_dump($na);
        $description_name = str_replace('"', '', $na[0]);
        $description_value = str_replace('"', '', $na[1]);
        $my_data_access_resource[$protocol]['uri'] = $uri;
        $my_data_access_resource[$protocol][$description_name] = $description_value;
      }
    }
  } return($my_data_access_resource);
}

function adc_parse_data_access_resource($data_access_resource) {
  $dar = [];
  foreach ($data_access_resource as $k => $v) {
    $va = explode(',', $v);
    $pu = explode(':', $va[0], 2);
    $dt = explode(':', $va[1], 2);
    $protocol = str_replace('"', '', $pu[0]);
    $dar[$protocol]['uri'] = str_replace('"', '', $pu[1]);
    $dar[$protocol]['description'] = str_replace('"', '', $dt[1]);
  } return($dar);
}

function adc_has_feature_type($metadata_identifier, $ft) {
  $feature_type = adc_get_feature_type($metadata_identifier);
  if ($feature_type === $ft) {
    return 1;
  } return 0;
}

function adc_get_feature_type($metadata_identifier) {
  $con = new \Drupal\metsis_lib\HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . SOLR_CORE_CONFIG . '/select', array("q" => METADATA_PREFIX . "metadata_identifier:" . '"' . $metadata_identifier . '"', "fl" => "feature_type", "wt" => "json", "indent" => "true"));
  $body = json_decode($res['body'], true);
  if ($body['response']['numFound'] > 0) {
    if (isset($body['response']['docs'][0]['feature_type'])) {
      return $body['response']['docs'][0]['feature_type'];
    }
  } return 0;
}

function adc_get_collections($collections) {
  $words = explode(",", $collections);
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

function metsis_get_metadata_table($solr_core, $metadata_identifier) {
  global $metsis_conf;
  $metadata_sort_order = $metsis_conf['metadata_sort_order'];
  $con = new \Drupal\metsis_lib\HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . $solr_core . '/select', array("q" => METADATA_PREFIX . "metadata_identifier:" . '"' . $metadata_identifier . '"', "wt" => "json", "indent" => "true"));
  $body = json_decode($res['body'], true);
  if (array_filter($body['response']['docs'][0])) {
    $metadata = array_filter($body['response']['docs'][0]);
    if (isset($metadata[METADATA_PREFIX . 'cloud_cover_value'])) {
      $metadata[METADATA_PREFIX . 'cloud_cover_value'] = number_format($metadata[METADATA_PREFIX . 'cloud_cover_value'], $metsis_conf['results_number_decimal_display_format']);
    }
    $zmetadata = array_intersect_key($metadata, array_flip($metsis_conf['metadata_visible']));
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
    }
    $sorted_top_array = array();
    foreach (array_values($metadata_sort_order) as $key) {
      if (key_exists($key, $top_array)) {
        $sorted_top_array[$key] = $top_array[$key];
      }
    }
    ksort($bottom_array);
    $top_rows = h_adc_get_metadata($sorted_top_array);
    $bottom_rows = h_adc_get_metadata($bottom_array);
    $rows = array_merge($top_rows, $bottom_rows);

    $metadata_table = array(
       '#type' => 'table',
       '#header' => $header,
       '#rows' => $rows,
       '#attributes' => array(
         'class' => array(
           'ext_data_souce'),
         ),

    );
    $markup = drupal_render($metadata_table);
    $metadata_inline = array(
      '#theme' => 'metadata_table'
    );
    $markup .= drupal_render($metadata_inline);
    return $markup;

  }
}
function metsis_get_metadata_div($solr_core, $metadata_identifier, $metadata_div_counter) {
  $button_text = defined('SOLR_METADATA_BUTTON_TEXT') ? t(SOLR_METADATA_BUTTON_TEXT) : t(Metadata);
  $metadata = '<div id="metadata-div-' . $metadata_div_counter;
  $metadata .= '" class="adc-button">' . $button_text . '<div class="metadata-div-' . $metadata_div_counter . ' initially-hidden';
  $metadata .= '">';
  $metadata .= metsis_get_metadata_table($solr_core, $metadata_identifier);
  $metadata .= '</div></div>';

  return $metadata;
}
function adc_get_metadata() {
  global $metsis_conf;
  $metadata_sort_order = $metsis_conf['metadata_sort_order'];
  $query_params = \Drupal::request()->query->all();
  $page_inputs = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_params);
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  if ($page_inputs['core'] == 'l1') {
    $core = SOLR_CORE_PARENT;
  }
  if ($page_inputs['core'] == 'l2') {
    $core = SOLR_CORE_CHILD;
  }
  $res = $con->get('/solr/' . $core . '/select', array("q" => METADATA_PREFIX . "metadata_identifier:" . '"' . $page_inputs['datasetID'] . '"', "wt" => "json", "indent" => "true"));
  $body = json_decode($res['body'], true);
  if (array_filter($body['response']['docs'][0])) {
    $metadata = array_filter($body['response']['docs'][0]);
    if (isset($metadata[METADATA_PREFIX . 'cloud_cover_value'])) {
      $metadata[METADATA_PREFIX . 'cloud_cover_value'] = number_format($metadata[METADATA_PREFIX . 'cloud_cover_value'], $metsis_conf['results_number_decimal_display_format']);
    }
    $zmetadata = array_intersect_key($metadata, array_flip($metsis_conf['metadata_visible']));
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
    }
    $sorted_top_array = array();
    foreach (array_values($metadata_sort_order) as $key) {
      if (key_exists($key, $top_array)) {
        $sorted_top_array[$key] = $top_array[$key];
      }
    }
    ksort($bottom_array);
    $top_rows = h_adc_get_metadata($sorted_top_array);
    $bottom_rows = h_adc_get_metadata($bottom_array);
    $rows = array_merge($top_rows, $bottom_rows);
    // @FIXME
    //var_dump($rows);
    $metadata_table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => array(
        'class' => array(
          'ext_data_souce'),
        ),
      );
    $markup = $metadata_table;
    //$markup = \Drupal::service('renderer')->render($metadata_table);
    //$metadata = array(
    //  '#theme' => 'metadata_table'
    //);
    //$markup .= $metadata;
    //$markup .= \Drupal::service('renderer')->render($metadata);
// @see https://www.drupal.org/node/2195739
    return $markup;

  }
}

/**
 * Build the rows for metadata information render array.
 *
 * FIXME: URLS and other HTML formatting get escaped when rendered.
 * need to add  code like new FormattableMarkup('<a class=@key href=@value>@value</a>', ['@value' => $value, '@key' => $key]);
 */
function h_adc_get_metadata($metadata) {
  $rows = array();

  foreach ($metadata as $key => $value) {

    if ($key == METADATA_PREFIX . "data_access_resource") {
      foreach ($value as $k => $v) {
        $la = explode(":", $v, 2);
        $ma = explode(",", $la[1]);
        if(isset($ma[1])) {
          $na = explode(":", $ma[1]);
        }
        else {
          $na = explode(":", $ma[0]);
        }
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
        }
        $href_url = str_replace('"', '', $ma[0]) . $url_ext;
        //var_dump($href_url);
        $res_string =  $protocol . ': ';
        //var_dump($res_string);
        $url = rtrim((string) $ma[0],"?");
        $link_markup = new FormattableMarkup('<a class="@key" href="@hrefurl">@url</a>',
        [ '@hrefurl' => $href_url, '@url' => $url, '@key' => $key]);
        //var_dump($link_markup);
        $value[$k] = $res_string . (string) $link_markup;
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
        } $value[$k] = $protocol . ':' . (string) new FormattableMarkup('<a class="@key" href="@value1">@value2</a>',
        [ '@value1' => str_replace('"', '', $ma[0]) . $url_ext, '@value2' => $ma[0], '@key' => $key]);
      }
    } if ($key == METADATA_PREFIX . "data_center_data_center_url") {
      //$value = '<a class="' . $key . '" href=' . $value . '>' . $value . '</a>';
        $value =  new FormattableMarkup('<a class=@key href=@value>@value</a>', ['@value' => $value, '@key' => $key]);
    } if ($key == METADATA_PREFIX . "data_center_contact_email") {
      $value = new FormattableMarkup('<a class=@key href=mailto:@value>@value</a>', ['@value' => $value, '@key' => $key]);
    } if ($key == METADATA_PREFIX . "personnel_email") {
      $value = new FormattableMarkup('<a class=@key href=mailto:@value>@value</a>', ['@value' => $value[0], '@key' => $key]);
    } if (is_array($value)) {
      $value = Markup::create(implode('<br>', $value));
      //$value = implode(new FormattableMarkup('@br', ['@br' => '<br/>']), $value);
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
  // @FIXME
  $options = [
    'headers' => [
      'Accept' => 'application/xml',
      ],
    ];
  //var_dump($url);
  //$options .= $built_query;
  $client = \Drupal::httpClient();
  //$request = $client->request('GET', $url);
  $res = NULL;
  try {
    $response = $client->get($url); //, $options);
    // Expected result.
    // getBody() returns an instance of Psr\Http\Message\StreamInterface.
    // @see http://docs.guzzlephp.org/en/latest/psr7.html#body
    if($response->getStatusCode() == '200') {
      $res = $response->getBody();
    }
    else {
      \Drupal::messenger()->addError("Failed to convert dataset to CSV format");
      //drupal_set_message();
        $request = \Drupal::request();
        $referer = $request->headers->get('referer');
        $response =  new RedirectResponse($referer);
        return $response->send();
    }
  }
  catch (RequestException | ServerException $e) {
    watchdog_exception('metsis_lib', $e);
    \Drupal::messenger()->addError("Failed to convert dataset to CSV format");
    //drupal_set_message();
      $request = \Drupal::request();
      $referer = $request->headers->get('referer');
      $response =  new RedirectResponse($referer);
      return $response->send();

  }

  //var_dump($res);
  $data = hack_xml_namespace($res);
  //var_dump($data);
  $xmldata = new SimpleXMLElement($data);
  $jsondata = \Drupal\Component\Serialization\Json::decode(\Drupal\Component\Serialization\Json::encode($xmldata));
  //var_dump($jsondata);
  return $jsondata;
}

function adc_get_datasets_fields($solr_server, $solr_port, $solr_core, $metadata_identifier_array, $fields_array, $start_row, $no_of_rows) {
  $fields = implode(',', $fields_array);
  $metadata_identifiers = implode('" OR "', $metadata_identifier_array);
  $metadata_identifiers = urlencode('("' . $metadata_identifiers . '")');
  $url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_metadata_identifier:' . $metadata_identifiers . '&fl=' . $fields . '&start=' . $start_row . '&rows=' . $no_of_rows . '&wt=json';
  \Drupal::logger('metsis_lib')->debug("URL  is: " . $url);
  $client = \Drupal::httpClient();
  $request = $client->get($url, ['timeout' =>  600]);
  $res = NULL;
  try {
    $response = $client->get($url);
    // Expected result.
    // getBody() returns an instance of Psr\Http\Message\StreamInterface.
    // @see http://docs.guzzlephp.org/en/latest/psr7.html#body
    $res = $response->getBody();
  }
  catch (RequestException $e) {
    watchdog_exception('metsis_lib', $e);
  }

  return json_decode($res, TRUE);
}

function adc_get_datasets_fields_reverse_lookup($solr_server, $solr_port, $solr_core, $mmd_metadata_identifier, $fields_array, $start_row, $no_of_rows) {
  $fields = implode(',', $fields_array);
  $url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_related_dataset:' . $mmd_metadata_identifier . '&fl=' . $fields . '&start=' . $start_row . '&rows=' . $no_of_rows . '&wt=json';
  $client = \Drupal::httpClient();
  //$request = $client->createRequest(array('GET', $url));
  $res = NULL;
  try {
    $response = $client->get($url);
    // Expected result.
    // getBody() returns an instance of Psr\Http\Message\StreamInterface.
    // @see http://docs.guzzlephp.org/en/latest/psr7.html#body
    $res = $response->getBody();
  }
  catch (RequestException $e) {
    watchdog_exception('metsis_lib', $e);
  }
  return json_decode($res, TRUE);
}

function adc_get_datasets_fields_mikey($solr_server, $solr_port, $solr_core, $metadata_identifier_array, $fields_array, $start_row, $no_of_rows) {
  $fields = implode(',', $fields_array);
  $metadata_identifiers = implode('" OR "', $metadata_identifier_array);
  $metadata_identifiers = urlencode('("' . $metadata_identifiers . '")');
  $url = 'http://' . $solr_server . ':' . $solr_port . '/solr/' . $solr_core . '/select?q=mmd_metadata_identifier:' . $metadata_identifiers . '&fl=' . $fields . '&start=' . $start_row . '&rows=' . $no_of_rows . '&wt=json';
  $client = \Drupal::httpClient();
  $request = $client->createRequest(array('GET', $url));
  $res = NULL;
  try {
    $response = $client->get($url);
    // Expected result.
    // getBody() returns an instance of Psr\Http\Message\StreamInterface.
    // @see http://docs.guzzlephp.org/en/latest/psr7.html#body
    $res = $response->getBody();
  }
  catch (RequestException $e) {
    watchdog_exception('metsis_lib', $e);
  }
  return json_decode($res, TRUE);
}

function msb_get_institutions() {
  $obj = msb_get_docs_fields_cond(array(METADATA_PREFIX . 'personnel_organisation'), 'mmd_collection:' . COLLECTIONS);
  $checkboxes = [];
  foreach ($obj['response']['docs'] as $doc) {
    if (count($doc) > 0) {
      if (!in_array($doc[METADATA_PREFIX . 'personnel_organisation'][0], $checkboxes)) {
        $checkboxes[] = $doc[METADATA_PREFIX . 'personnel_organisation'][0];
      }
    }
  }
  sort($checkboxes);
  return $checkboxes;
}

function msb_facet_get_collections() {
  global $metsis_conf;
  if (isset($metsis_conf['collections'])) {
    $collections_to_display = array_map('trim', explode(',', $metsis_conf['collections']));
  } $checkboxes = [];
  $url = 'http://' . SOLR_SERVER_IP . ':' . SOLR_SERVER_PORT . '/solr/' . SOLR_CORE_PARENT . '/select?q=*:*&rows=1&wt=json&facet=true&facet.field=mmd_collection';
  $client = \Drupal::httpClient();
  //$request = $client->createRequest('GET', $url, []);
  $res = NULL;
  try {
    $response = $client->get($url);
    // Expected result.
    // getBody() returns an instance of Psr\Http\Message\StreamInterface.
    // @see http://docs.guzzlephp.org/en/latest/psr7.html#body
    $res = $response->getBody();
  }
  catch (RequestException $e) {
    watchdog_exception('metsis_lib', $e);
  }

  $json = json_decode($res);
  while ($current = current($json->facet_counts->facet_fields->mmd_collection)) {
    $cstring = (string) $current . '  (' . (string) next($json->facet_counts->facet_fields->mmd_collection) . ')';
    if (isset($collections_to_display)) {
      if (in_array($current, $collections_to_display)) {
        $checkboxes[] = $cstring;
      }
    }
    else {
      $checkboxes[] = $cstring;
    } $current = next($json->facet_counts->facet_fields->mmd_collection);
  } return $checkboxes;
}

function msb_get_chosen_institutions($form_state) {
  $q = "";
  if (INSTITUTIONS_VISIBLE) {
    $chosen_institutions = array_filter($form_state->getValue('institutions')['chosen_institutions']);
    if (!empty($chosen_institutions)) {
      $q = "mmd_personnel_organisation:(\"" . implode("\" OR \"", $chosen_institutions) . "\")";
    }
  } return $q;
}

function msb_get_chosen_collections($form_state) {
  $q = "";
  if (COLLECTIONS_VISIBLE) {
    $chosen_collections = array_filter($form_state->getValue('collections')['chosen_collections']);
    if (!empty($chosen_collections)) {
      $stripped_cc = [];
      foreach ($chosen_collections as $cc) {
        $stripped_cc[] = explode(' ', $cc)[0];
      };
      $q = "mmd_collection:(\"" . implode("\" OR \"", $stripped_cc) . "\")";
    }
  } return $q;
}

function adc_get_link_list($href_array, $display_text_array) {
  $it = new MultipleIterator();
  $it->attachIterator(new ArrayIterator($href_array));
  $it->attachIterator(new ArrayIterator($display_text_array));
  $div = '<div id="adcllist">';
  $div .= '<ul>';
  foreach ($it as $a) {
    if (preg_match('/(<img).*/i', $a[1]) === 1) {
      $div .= '<li><a href="' . $a[0] . '">' . $a[1] . '</a></li>';
    }
    else {
      $div .= '<li><a class="adc-button adc-sbutton" href="' . $a[0] . '">' . $a[1] . '</a></li>';
    }
  } $div .= '</ul>';
  $div .= '</div>';
  return $div;
}

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
  } return($this_kv);
}

function adc_get_md_kv($solr_core_level, $dataset_id) {
  global $base_url;
  $md_kv = [];
  if (defined('SOLR_METADATA_BUTTON_TEXT')) {
    $solr_metadata_button_text = SOLR_METADATA_BUTTON_TEXT;
  }
  else {
    $solr_metadata_button_text = "Metadata";
  } $md_kv['href'] = $base_url . '/metsis/display/metadata/?core=' . $solr_core_level . '&datasetID=' . $dataset_id;
  $md_kv['display_text'] = $solr_metadata_button_text;
  return $md_kv;
}

function msb_get_md_links($solr_core_level, $dataset_id) {
  global $base_url;
  if (defined('SOLR_METADATA_BUTTON_TEXT')) {
    $solr_metadata_button_text = SOLR_METADATA_BUTTON_TEXT;
  }
  else {
    $solr_metadata_button_text = "Metadata";
  }
  $url = '' . '<div class="botton-wrap">' . '<a class="adc-button adc-sbutton ext_data_source" href="' . $base_url . '/metsis/display/metadata/?core=' . $solr_core_level . '&datasetID=' . $dataset_id . '" >' . $solr_metadata_button_text . '</a>' . '</div>';
  return $url;
}

function adc_get_title_kv($solr_obj, $metadata_identifier) {
  $title_kv = [];
  $title_kv['metadata_identifier'] = $metadata_identifier;
  foreach ($solr_obj['response']['docs'] as $so) {
    if ($so['mmd_metadata_identifier'] == $metadata_identifier) {
      $title_kv['title'] = $so['mmd_title'][0];
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
  } return($title_kv);
}

function adc_get_thumbnail_kv($solr_obj, $metadata_identifier) {
  $thumbnail_kv = [];
  $thumbnail_kv['metadata_identifier'] = $metadata_identifier;
  foreach ($solr_obj['response']['docs'] as $so) {
    if ($so['mmd_metadata_identifier'] == $metadata_identifier) {
      if (isset($so['feature_type'])) {
        $thumbnail_kv['feature_type'] = $so['feature_type'];
      } $thumbnail_kv['thumbnail_data'] = $so['thumbnail_data'];
      $thumbnail_kv['display_text'] = '<img src="' . $so['thumbnail_data'] . '"';
    }
  } return($thumbnail_kv);
}

function msb_get_docs_fields($fields) {
  $fl = implode(",", $fields);
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => "*:*", "rows" => SEARCH_MAXIMUM_ROWS_TO_FETCH, "wt" => "json", "fl" => "$fl",));
  return json_decode($res['body'], true);
}

function msb_get_docs_fields_cond($fields, $condition) {
  $fl = implode(",", $fields);
  $con = new HttpConnection(SOLR_SERVER_IP, SOLR_SERVER_PORT);
  $res = $con->get('/solr/' . SOLR_CORE_PARENT . '/select', array("q" => $condition, "rows" => SEARCH_MAXIMUM_ROWS_TO_FETCH, "wt" => "json", "fl" => "$fl",));
  return json_decode($res['body'], true);
}

function adc_array_to_solror($trimmed_array) {
  $qs = '("';
  $qs .= implode('" OR "', $trimmed_array);
  $qs .= '")';
  return $qs;
}

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
    } if (isset($found_in_child_core['response']['docs'])) {
      foreach ($found_in_child_core['response']['docs'] as $doc) {
        if ($doc[METADATA_PREFIX . 'metadata_identifier'] == $mi) {
          $data[$mi] = SOLR_CORE_CHILD;
        }
      }
    }
  } return $data;
}

function adc_has_related_dataset($solr_core, $metadata_identifier_array) {
  $has_related_dataset = [];
  $rd_check = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, $solr_core, $metadata_identifier_array, array(METADATA_PREFIX . 'metadata_identifier', METADATA_PREFIX . 'related_dataset', METADATA_PREFIX . 'title'), 0, 1000000);
  foreach ($rd_check['response']['docs'] as $doc) {
    if (isset($doc[METADATA_PREFIX . 'related_dataset'])) {
      if (count($doc[METADATA_PREFIX . 'related_dataset']) > 0) {
        $has_related_dataset[$doc[METADATA_PREFIX . 'metadata_identifier']] = $doc[METADATA_PREFIX . 'title'];
      }
    }
  } return $has_related_dataset;
}

function msb_concat_data_access_resource($data_access_resource_array) {
  $gar = [];
  foreach ($data_access_resource_array as $dara) {
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
    $patterns = [];
    $patterns[0] = '/(\s)/';
    $patterns[1] = '/(\")/';
    $replacements = [];
    $replacements[0] = '_';
    $replacements[1] = '';
    $dara[0] = preg_replace($patterns, $replacements, $dara[0]);
    $dara[1] = preg_replace($patterns[1], $replacements[1], $dara[1]);
    $fara[0] = preg_replace($patterns, $replacements, $fara[0]);
    $fara[1] = preg_replace($patterns[1], $replacements[1], $fara[1]);
    $eara[0] = preg_replace($patterns, $replacements, $eara[0]);
    $eara[1] = preg_replace($patterns[1], $replacements[1], $eara[1]);
    if ($dara[1] == "URL") {
      $dara[1] = MISSING_METADATA;
    }
    $gar[$dara[0]]['url'] = $eara[0];
    $gar[$dara[0]][$fara[0]] = $fara[1];
  }
  return $gar;
}

function adc_get_core_name_from_basket($metadata_identifier) {
  $result = \Drupal::database()->select('metsis_basket', 'mb')->fields('mb', ['solr_core'])->execute()->fetchAll();
  return $result;
}

function test00() {


}

function metsis_set_session_var($name, $value) {
  $name = session_id() . '__metsis__' . $name;
  $tempstore = \Drupal::service('tempstore.private')->get('metsis');
  $tempstore->delete($name);
  $tempstore->set($name, $value);
}

function metsis_get_session_var($name) {
    $name = session_id() . '__metsis__' . $name;
    $tempstore = \Drupal::service('tempstore.private')->get('metsis');
    return $tempstore->get($name, $value);

}

function metsis_del_session_var($name) {
  $name = session_id() . '__metsis__' . $name;
  $tempstore = \Drupal::service('tempstore.private')->get('metsis');
  $tempstore->delete($name);

}

function adc_get_map_base_layer($top_left_lat, $bottom_right_lat, $tol = 1.0e-10) {

  if ((abs($bottom_right_lat) > $tol) && ($bottom_right_lat > 0) && ($top_left_lat > 0)) {
    return ("north");
  }
  if ((abs($top_left_lat) > $tol) && ($top_left_lat < 0) && ($bottom_right_lat < 0)) {
    return ("south");
  }
  else {
    return ("world");
  }
}

/**
 * test{
 */
//function metsis_get_session_var($name) {
//  $name = session_id() . '__metsis__' . $name;
//  return array_map('unserialize', db_query('SELECT name, value FROM {metsis_variable}')->fetchAllKeyed());
//}
//
//function metsis_set_session_var($name, $value) {
//    $name = session_id() . '__metsis__' . $name;
//  db_merge('metsis_variable')->key(array('name' => $name))->fields(array('value' => serialize($value)))->execute();
//}
//
//function metsis_del_session_var($name) {
//  $name = session_id() . '__metsis__' . $name;
//  db_delete('metsis_variable')
//    ->condition('name', $name)
//    ->execute();
//}
/**
 * test}
 */

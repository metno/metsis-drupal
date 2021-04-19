<?php

function metsis_basket_download_odata_action_info() {
  $action = array('metsis_basket_download_odata_action' => array('type' => 'entity', 'label' => t('ADC download ODATA items'), 'behavior' => array('changes_property'), 'configurable' => FALSE, 'vbo_configurable' => FALSE, 'pass rows' => TRUE, 'triggers' => array('any'), 'aggregate' => TRUE,),);
  return $action;
}

function metsis_basket_download_odata_action(&$node, $context) {
  $records = array_keys($node);
  $uris = get_download_odata_action_uris("metsis_basket", $records, "odata");
  create_download_odata_action_order($uris);
}

function get_download_odata_action_uris($table, $records, $resource) {
  $uris = array();
  $data_access_resource = "data_access_resource";
  switch ($resource) {
    case "odata": $data_access_resource = $data_access_resource . "_odata";
      break;
  } foreach ($records as $record) {
    $query = \Drupal::database()->select($table, 't');
    $query->condition('t.iid', $record, '=')->fields('t', array($data_access_resource));
    $result = $query->execute();
    foreach ($result as $r) {
      $uris[] = $r->$data_access_resource;
    }
  } return $uris;
}

function create_download_odata_action_order($uris) {
  $user = \Drupal::currentUser();
  global $base_url;
  global $metsis_conf;
  $req_params = array('userId' => $user->name, 'email' => $user->mail, 'site' => $metsis_conf['drupal_site_data_center_desc'] ? $metsis_conf['drupal_site_data_center_desc'] : $base_url, 'format' => $metsis_conf['default_data_archive_format'] ? $metsis_conf['default_data_archive_format'] : "tgz", 'uri' => implode(";", $uris),);
  $receipt = adc_basket_query(METSIS_BASKET_SERVER, METSIS_BASKET_SERVER_PORT, METSIS_BASKET_SERVER_SERVICE, $req_params);
  // @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
//
//
// @see https://www.drupal.org/node/2195739
// \Drupal::messenger()->addMessage(t('Your download request has been queued for processing. An email with further instructions will be sent to:!values', array('!values' => theme('item_list', array('items' => array($receipt['email']),)))));

  if(DEBUG == 1) {
/*    \Drupal::logger('METSIS basket request parameters')->debug('<pre>' . print_r($req_params, TRUE) . '</pre>', []);
    \Drupal::logger('METSIS basket response')->debug('<pre>' . print_r($receipt, TRUE) . '</pre>', []);
*/
  }
}

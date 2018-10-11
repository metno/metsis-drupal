<?php
 function metsis_basket_download_action_info() { $action = array( 'metsis_basket_download_action' => array( 'type' => 'entity', 'label' => t('ADC download items'), 'behavior' => array('changes_property'), 'configurable' => FALSE, 'vbo_configurable' => FALSE, 'pass rows' => TRUE, 'triggers' => array('any'), 'aggregate' => TRUE, ), ); return $action; } function metsis_basket_download_action(&$node, $context) { $records = array_keys($node); $uris = get_download_action_uris("metsis_basket", $records, "http"); create_download_action_order($uris); } function get_download_action_uris($table, $records, $resource) { $uris = array(); $data_access_resource = "data_access_resource"; switch ($resource) { case "http": $data_access_resource = $data_access_resource . "_http"; break; } foreach ($records as $record) { $query = db_select($table, 't'); $query->condition('t.iid', $record, '=') ->fields('t', array($data_access_resource)); $result = $query->execute(); foreach ($result as $r) { $uris[] = $r->$data_access_resource; } } return $uris; } function create_download_action_order($uris) { global $user; global $base_url; global $metsis_conf; $req_params = array( 'userId' => $user->name, 'email' => $user->mail, 'site' => $metsis_conf['drupal_site_data_center_desc'] ? $metsis_conf['drupal_site_data_center_desc'] : $base_url, 'format' => $metsis_conf['default_data_archive_format'] ? $metsis_conf['default_data_archive_format'] : "tgz", 'uri' => implode(";", $uris), ); $receipt = adc_basket_query(METSIS_BASKET_SERVER, METSIS_BASKET_SERVER_PORT, METSIS_BASKET_SERVER_SERVICE, $req_params); drupal_set_message(t('Your download request has been queued for processing. An email with further instructions will be sent to:!values', array( '!values' => theme('item_list', array( 'items' => array($receipt['email']), )) ))); } 
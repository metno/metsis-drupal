<?php

/* * ***************************************************
 * this action is deprecated and is not longer in use. 
 * Use download_http.action or download_odata.action 
 * instead.
 * *************************************************** */

/**
 * Implements hook_action_info().
 */
function metsis_basket_dispatch_action_info() {
    $action = array(
      'dispatch_action' => array(
        'type' => 'entity',
        'label' => t('ADC Dispatch items'),
        'behavior' => array('changes_property'),
        'configurable' => TRUE,
        'vbo_configurable' => FALSE,
        'pass rows' => TRUE,
        'triggers' => array('any'),
        'aggregate' => TRUE,
      ),
    );
    return $action;
}

//function dispatch_action(&$entity, $context = array()) {
//    
//}

function dispatch_action_form($context, &$form_state) {
    $vbo = _views_bulk_operations_get_field($form_state['build_info']['args'][0]);
    $selections = _views_bulk_operations_get_selection($vbo, $form_state);
//    var_dump($selections);
//    sdpm($selections);
    $uris = get_data_access_resource("metsis_basket", $selections, "odata");
//    $uris = get_data_access_resource("metsis_basket", $selections, "httpserver");
    create_order($uris);
    $form = array();
//  $form['user'] = array(
//    '#type' => 'textfield',
//    '#title' => t('User'),
//    '#maxlength' => 60,
//    '#autocomplete_path' => 'user/autocomplete',
//    '#weight' => -1,    
//  );
//$form['submit'] = array(
//    '#type' => 'submit',
//    '#value' => t('Back to view'),
//    '#submit' => array('metsis_basket_foo'),
//    '#weight' => 17,
//  );

    return $form;
}

// utils
/*
 * $table = string: name of basket table 
 * $records = array: of iid sent from views bulk action
 * $resource = string: one of "http", "opendap", "wms"
 * 
 */


function get_data_access_resource($table, $records, $resource) {
    //$table = "metsis_basket";
    $data_access_resource = "data_access_resource";
    $uris = array();
    switch ($resource) {
        case "http":
            $data_access_resource = $data_access_resource . "_http";
            break;
        case "opendap":
            $data_access_resource = $data_access_resource . "_opendap";
            break;
        case "ogc_wms":
            $data_access_resource = $data_access_resource . "_ogc_wms";
            break;
        case "odata":
            $data_access_resource = $data_access_resource . "_odata";
            break;
    }
    //select b.data_access_resource_http from metsis_basket as b

    foreach ($records as $record) {
        $query = db_select($table, 't');
        $query->condition('t.iid', $record, '=')
            ->fields('t', array($data_access_resource));
        //->range(0, 50);
        $result = $query->execute();
        foreach ($result as $r) {
            $uris[] = $r->$data_access_resource;
        }
    }
    return $uris;
}

//TODO: refactor
function create_order($uris) {
    global $user;
    global $base_url;
    global $metsis_conf;
    $req_params = array(
      'userId' => $user->name,
      'email' => $user->mail,
      'site' => $metsis_conf['drupal_site_data_center_desc'] ? $metsis_conf['drupal_site_data_center_desc'] : $base_url,
      'format' => $metsis_conf['default_data_archive_format'] ? $metsis_conf['default_data_archive_format'] : "tgz",
      'uri' => implode(";", $uris),
    );
    $receipt = adc_basket_query(METSIS_BASKET_SERVER, METSIS_BASKET_SERVER_PORT, METSIS_BASKET_SERVER_SERVICE, $req_params);
//    sdpm($receipt);
//    $order = "http://"
//        . METSIS_BASKET_SERVER
//        . ":"
//        . METSIS_BASKET_SERVER_PORT
//        . METSIS_BASKET_SERVER_SERVICE
//        . "?"
//        . "userId="
//        . $user->name
//        . "&email="
//        . $user->mail
//        . "&site=sios&format=tgz&uri=";
//    foreach ($uris as $u) {
//        $order = $order . $u;
//        $order = $order . ";";
//    }
//    sdpm($order);
//    $receipt = drupal_http_request($order);
//    $data = new SimpleXMLElement($receipt->data);
//    $data = drupal_json_decode(drupal_json_encode($data));
//    drupal_set_message("Your request is being processed. An email will be sent to " . $data['email'] . " with instructions once your order is ready for download.", 'status');
//
//    return $order;
}

//
//function dispatch_order($request) {
//  
//}
//
function dispatch_action_submit() {
    drupal_set_message("dispatched", 'status');

    //$form_state['redirect'] = array('path' => $vbo->view->get_url(), array('query' => $query));
    $form_state['redirect'] = array('https://xyz.metsis.met.no');
}

//
//function metsis_basket_foo(){
//  $form_state['redirect'] = array('metsis_basket-custom-entity-view-01/0');
//}
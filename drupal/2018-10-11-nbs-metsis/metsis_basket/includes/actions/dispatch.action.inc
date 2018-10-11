<?php

function metsis_basket_dispatch_action_info() {
    $action = array('dispatch_action' => array('type' => 'entity', 'label' => t('ADC Dispatch items'), 'behavior' => array('changes_property'), 'configurable' => TRUE, 'vbo_configurable' => FALSE, 'pass rows' => TRUE, 'triggers' => array('any'), 'aggregate' => TRUE,),);
    return $action;
}

function dispatch_action_form($context, &$form_state) {
    $vbo = _views_bulk_operations_get_field($form_state['build_info']['args'][0]);
    $selections = _views_bulk_operations_get_selection($vbo, $form_state);
    $uris = get_data_access_resource("metsis_basket", $selections, "odata");
    create_order($uris);
    $form = array();
    return $form;
}

function get_data_access_resource($table, $records, $resource) {
    $data_access_resource = "data_access_resource";
    $uris = array();
    switch ($resource) {
        case "http": $data_access_resource = $data_access_resource . "_http";
            break;
        case "opendap": $data_access_resource = $data_access_resource . "_opendap";
            break;
        case "ogc_wms": $data_access_resource = $data_access_resource . "_ogc_wms";
            break;
        case "odata": $data_access_resource = $data_access_resource . "_odata";
            break;
    } foreach ($records as $record) {
        $query = db_select($table, 't');
        $query->condition('t.iid', $record, '=')->fields('t', array($data_access_resource));
        $result = $query->execute();
        foreach ($result as $r) {
            $uris[] = $r->$data_access_resource;
        }
    } return $uris;
}

function create_order($uris) {
    global $user;
    global $base_url;
    global $metsis_conf;
    $req_params = array('userId' => $user->name, 'email' => $user->mail, 'site' => $metsis_conf['drupal_site_data_center_desc'] ? $metsis_conf['drupal_site_data_center_desc'] : $base_url, 'format' => $metsis_conf['default_data_archive_format'] ? $metsis_conf['default_data_archive_format'] : "tgz", 'uri' => implode(";", $uris),);
    $receipt = adc_basket_query(METSIS_BASKET_SERVER, METSIS_BASKET_SERVER_PORT, METSIS_BASKET_SERVER_SERVICE, $req_params);
}

function dispatch_action_submit() {
    drupal_set_message("dispatched", 'status');
    $form_state['redirect'] = array('https://xyz.metsis.met.no');
}

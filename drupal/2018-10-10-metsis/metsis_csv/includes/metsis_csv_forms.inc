<?php

function csv_config_form($form, &$form_state) {
    global $metsis_conf;
    $metadata_identifier = isset($_GET['metadata_identifier']) ? check_plain($_GET['metadata_identifier']) : '';
    if (defined('CSV_NPOINTS')) {
        $default_csv_npoints = CSV_NPOINTS;
    } $form = array();
    $odv_object = adc_get_odv_object(adc_get_od_variables($metadata_identifier, SOLR_CORE_PARENT)['data']['findAllVariables']);
    $form['od_variables'] = array('#type' => 'container',);
    $header = array('standard_name' => t('Standard name'), 'units' => t('Units'),);
    foreach ($odv_object as $odvo) {
        if (key_exists('standard_name', $odvo)) {
            if (in_array(trim($odvo['standard_name']), $metsis_conf['csv_exclude_variables'])) {
                continue;
            } $options[$odvo['standard_name']] = array('standard_name' => $odvo['standard_name'], 'units' => $odvo['units'],);
        }
    } ksort($options);
    $form['od_variables_tabular'] = array('#type' => 'container',);
    $form['od_variables_tabular']['selected_variables'] = array('#type' => 'tableselect', '#header' => $header, '#options' => $options, '#attributes' => array('class' => array('csv-vars-table')),);
    $form['opendap_uri'] = array('#type' => 'hidden', '#disabled' => true, '#default_value' => adc_get_data_access_resource($metadata_identifier)['OPeNDAP']['uri'],);
    $form['csv_npoints'] = array('#type' => 'hidden', '#default_value' => $default_csv_npoints, '#size' => 7,);
    $form['csv_file_format'] = array('#type' => 'select', '#options' => array('csv' => 'CSV',), '#default_value' => 'csv', '#description' => t(''), '#empty' => t(''),);
    $form['submit'] = array('#type' => 'submit', '#value' => t('Submit'), '#submit' => array('metsis_csv_submit'),);
    $form['#validate'][] = 'mcsv_var_select_validate';
    if (isset($form_state['storage']['results'])) {
        $form['results'] = array('#value' => $form_state['storage']['results'],);
    } return $form;
}

function metsis_csv_submit($form, &$form_state) {
    $receipt = adc_get_csv_query($form_state);
    foreach ($receipt['wps_ProcessOutputs']['wps_Output'] as $wpsPO) {
        if (in_array_r('CSVOutputFileURL', $wpsPO)) {
            $results[] = array($wpsPO['wps_Data']['wps_LiteralData']);
        }
    } $results[] = array("vars passed in from form to form");
    $form_state['storage']['results'] = $results;
    $form_state["rebuild"] = TRUE;
}

function adc_csv_wps_query($server, $port, $service, $req_params) {
    $con = new HttpConnection($server, $port);
    $res = $con->get($service, $req_params);
    $res_body = hack_xml_namespace($res['body']);
    $res_body_xml = new SimpleXMLElement($res_body);
    $jres_body = drupal_json_decode(drupal_json_encode($res_body_xml));
    return $jres_body;
}

function adc_get_csv_query($form_state) {
    $req_params = array('ServiceProvider' => CSV_SERVICE_PROVIDER, 'metapath' => CSV_METAPATH, 'Service' => CSV_SERVICE_NAME, 'Request' => CSV_REQUEST, 'Version' => CSV_WPS_VERSION, 'Identifier' => CSV_IDENTIFIER, 'datainputs' => adc_get_csv_datainputs($form_state),);
    $built_query = http_build_query($req_params);
    return adcwps_query(CSV_SERVER_PROTOCOL, CSV_SERVER, CSV_SERVICE_PATH, $built_query);
}

function adc_get_csv_datainputs($form_state) {
    $selected_variables = [];
    foreach ($form_state['input']['selected_variables'] as $sv) {
        if ($sv != NULL) {
            array_push($selected_variables, $sv);
        }
    } $datainputs_array = array('everyNth' => $form_state['input']['csv_npoints'], 'fileFormat' => $form_state['input']['csv_file_format'], 'varSNList' => implode("+", $selected_variables), 'fileName' => adc_get_random_file_name(), 'odurl' => $form_state['complete form']['opendap_uri']['#value'],);
    $tmp_datainputs = [];
    foreach ($datainputs_array as $k => $v) {
        array_push($tmp_datainputs, $k . "=" . $v);
    } return implode(";", $tmp_datainputs);
}

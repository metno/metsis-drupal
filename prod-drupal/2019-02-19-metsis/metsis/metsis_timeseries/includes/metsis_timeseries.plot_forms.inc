<?php

function ts_config_form($form, &$form_state) {
    global $metsis_conf;
    $metadata_identifier = isset($_GET['metadata_identifier']) ? check_plain($_GET['metadata_identifier']) : '';
    $form = array();
    $odv_standard_names = adc_get_odv_standard_names(adc_get_odv_object(adc_get_od_variables($metadata_identifier, SOLR_CORE_PARENT)['data']['findAllVariables']), $metsis_conf['ts_exclude_variables']);
    ksort($odv_standard_names);
    if (adc_has_feature_type($metadata_identifier, 'timeSeries') === 1 || in_array('time', $odv_standard_names)) {
        $default_x_axis = 'time';
    }
    else {
        $default_x_axis = '';
    } if (in_array('air_temperature', $odv_standard_names)) {
        $default_y_axis = 'air_temperature';
    }
    else {
        $default_y_axis = '';
    } if (defined('TS_PLOT_NPOINTS')) {
        $default_ts_plot_npoints = TS_PLOT_NPOINTS;
    }
    else {
        $default_ts_plot_npoints = '';
    } $form['opendap_uri'] = array('#type' => 'hidden', '#disabled' => true, '#default_value' => adc_get_data_access_resource(SOLR_CORE_PARENT, $metadata_identifier)['OPeNDAP']['uri'],);
    $form['metadata_identifier'] = array('#type' => 'hidden', '#disabled' => true, '#default_value' => $metadata_identifier,);
    $form['ts_plot_npoints'] = array('#type' => 'textfield', '#default_value' => $default_ts_plot_npoints, '#size' => 7,);
    $form['ts_plot_file_format'] = array('#type' => 'select', '#options' => array('png' => 'PNG', 'svg' => 'SVG',), '#default_value' => 'png', '#description' => t(''), '#empty' => t(''),);
    $form['x_axis'] = array('#type' => 'select', '#options' => $odv_standard_names, '#default_value' => $default_x_axis, '#description' => t(''), '#empty' => t(''),);
    $form['y_axis'] = array('#type' => 'select', '#options' => $odv_standard_names, '#default_value' => $default_y_axis, '#description' => t(''), '#empty' => t(''),);
    $form['submit'] = array('#type' => 'submit', '#value' => t('Submit'), '#submit' => array('metsis_timeseries_submit'),);
    if (isset($form_state['storage']['results'])) {
        $form['results'] = array('#value' => $form_state['storage']['results'],);
    } return $form;
}

function metsis_timeseries_submit($form, &$form_state) {
    $receipt = adc_get_ts_query($form_state);
    $results[] = array($receipt['wps_ProcessOutputs']['wps_Output']['wps_Data']['wps_LiteralData']);
    $form_state['storage']['results'] = $results;
    $form_state["rebuild"] = TRUE;
}

function adc_get_ts_query($form_state) {
    $req_params = array('ServiceProvider' => TS_SERVICE_PROVIDER, 'metapath' => TS_METAPATH, 'Service' => TS_SERVICE_NAME, 'Request' => TS_REQUEST, 'Version' => TS_WPS_VERSION, 'Identifier' => TS_IDENTIFIER, 'datainputs' => adc_get_ts_datainputs($form_state),);
    $built_query = http_build_query($req_params);
    return adcwps_query(TS_SERVER_PROTOCOL, TS_SERVER, TS_SERVICE_PATH, $built_query);
}

function adc_get_ts_datainputs($form_state) {
    $datainputs_array = array('xvar' => $form_state['input']['x_axis'], 'yvar' => $form_state['input']['y_axis'], 'everyNth' => $form_state['input']['ts_plot_npoints'], 'fileFormat' => $form_state['input']['ts_plot_file_format'], 'fileName' => adc_get_random_file_name(), 'odurl' => $form_state['complete form']['opendap_uri']['#value'],);
    $tmp_datainputs = [];
    foreach ($datainputs_array as $k => $v) {
        array_push($tmp_datainputs, $k . "=" . $v);
    } return implode(";", $tmp_datainputs);
}

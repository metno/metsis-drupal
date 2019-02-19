<?php

function metsis_basket_visualize_action_info() {
    $action = array('metsis_basket_visualize_action' => array('type' => 'entity', 'label' => t('ADC visualize items'), 'behavior' => array('changes_property'), 'configurable' => FALSE, 'vbo_configurable' => FALSE, 'pass rows' => TRUE, 'triggers' => array('any'), 'aggregate' => TRUE,),);
    return $action;
}

function metsis_basket_visualize_action(&$node, $context) {
    $metadata_identifiers = [];
    foreach ($node as $n) {
        $metadata_identifiers[] = $n->metadata_identifier;
    } $solr_core = adc_get_solr_core(array($metadata_identifiers[0]));
    $options = array('query' => array('dataset' => implode(",", $metadata_identifiers), 'solr_core' => $solr_core[$metadata_identifiers[0]], 'calling_results_page' => BASKET_ENDPOINT,),);
    if (count($metadata_identifiers > 1)) {
        drupal_set_message('Time series plotting for basket items is not fully implemented.', 'warning');
    } if (adc_has_feature_type($metadata_identifiers[0], 'timeSeries') === 1) {
        $options['query']['metadata_identifier'] = $metadata_identifiers[0];
        drupal_goto(TS_ENDPOINT, $options);
    }
    else {
        drupal_goto(WMS_ENDPOINT, $options);
    }
}

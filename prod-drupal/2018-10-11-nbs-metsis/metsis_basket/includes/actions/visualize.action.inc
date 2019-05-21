<?php

function metsis_basket_visualize_action_info() {
    $action = array('metsis_basket_visualize_action' => array('type' => 'entity', 'label' => t('ADC visualize items'), 'behavior' => array('changes_property'), 'configurable' => FALSE, 'vbo_configurable' => FALSE, 'pass rows' => TRUE, 'triggers' => array('any'), 'aggregate' => TRUE,),);
    return $action;
}

function metsis_basket_visualize_action(&$node, $context) {
    $metadata_identifiers = [];
    foreach ($node as $n) {
        $metadata_identifiers[] = $n->metadata_identifier;
    } $options = array('query' => array('dataset' => implode(",", $metadata_identifiers),),);
    drupal_goto(WMS_ENDPOINT, $options);
}
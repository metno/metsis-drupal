<?php

/**
 * Implements hook_action_info().
 */
function metsis_basket_transform_action_info() {
    $action = array(
      'metsis_basket_transform_action' => array(
        'type' => 'entity',
        'label' => t('ADC Transform items'),
        //'permissions' => array('access content', 'administer site configuration'),
        'behavior' => array('changes_property'),
        'configurable' => FALSE,
        'vbo_configurable' => FALSE,
        'pass rows' => TRUE,
        'triggers' => array('any'),
        'aggregate' => TRUE,
      ),
    );
    return $action;
}

function metsis_basket_transform_action(&$node, $context) {


    $metadata_identifiers = [];
    foreach ($node as $n) {
        $metadata_identifiers[] = $n->metadata_identifier;
    }
    $options = array(
      'query' => array(
        'dataset_id' => implode(",", $metadata_identifiers),
      ),
    );
    drupal_goto(TRANSFORMATION_ENDPOINT, $options);
}

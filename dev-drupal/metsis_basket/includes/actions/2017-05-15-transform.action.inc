<?php

/**
 * Implements hook_action_info().
 */
function metsis_basket_transform_action_info() {
    $action = array(
      'transform_action' => array(
        'type' => 'entity',
        'label' => t('ADC Transform items'),
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

function transform_action_form($context, &$form_state) {
    $vbo = _views_bulk_operations_get_field($form_state['build_info']['args'][0]);
    $selections = _views_bulk_operations_get_selection($vbo, $form_state);
    $form = array();
    return $form;
}

function transform_action_submit($form, &$form_state) {
    transform_metsis_db_datasets($form_state['build_info']['args'][0]->base_table, $form_state['selection']);
}

function transform_metsis_db_datasets($table, $pkey_array) {
    global $user;
    $dataset_ids = [];
    foreach ($pkey_array as $pk) {
        $results = db_select($table, 't')
            ->condition('t.iid', $pk)
            ->condition('t.uid', $user->uid)
            ->fields('t', array('metadata_identifier'))
            ->execute();
        foreach ($results as $r) {
            $dataset_ids[] = $r->metadata_identifier;
        }
    }
    $options = array(
      'query' => array(
        'dataset_id' => implode(",", $dataset_ids),
      ),
    );
    drupal_goto(TRANSFORMATION_ENDPOINT, $options);
}

<?php

/**
 * Implements hook_action_info().
 */
function metsis_basket_zyx_action_info() {
    $action = array(
      'zyx_delete_action' => array(
        'type' => 'entity',
        'label' => t('ZYX Delete items'),
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

function zyx_delete_action_form($context, &$form_state) {
//    $vbo = _views_bulk_operations_get_field($form_state['build_info']['args'][0]);
//    $selections = _views_bulk_operations_get_selection($vbo, $form_state);
    $form = array();
    //sdpm($form_state['selection']);
    $form['log'] = array(
      '#type' => 'checkbox',
      '#title' => t('Log individual deletions'),
      '#description' => t('Note: Deleting large amounts of entities will generate large amounts of log messages.'),
      '#default_value' => !empty($settings['log']),
    );

    return $form;
}

function zyx_delete_action_submit($form, &$form_state) {
    //$vbo = _views_bulk_operations_get_field($form_state['build_info']['args'][0]);
    //$selections = _views_bulk_operations_get_selection($vbo, $form_state);
    zyx_del_db_records($form_state['build_info']['args'][0]->base_table, $form_state['selection']);
}

function zyx_del_db_records($table, $pkey_array) {
    global $user;
    foreach ($pkey_array as $pk) {
        db_delete($table)
            ->condition('iid', $pk)
            ->condition('uid', $user->uid)
            //need more condition to ensure that the user deletes correct records
            //we could give the user a form in the confirm stage, but that would
            //probably be unwelcome
            ->execute();
    }
}

<?php

/**
 * Implements hook_action_info().
 */
function metsis_basket_empty_action_info() {
    $action = array(
      'empty_action' => array(
        'type' => 'entity',
        'label' => t('ADC Empty basket'),
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

function empty_action_form($context, &$form_state) {
    $vbo = _views_bulk_operations_get_field($form_state['build_info']['args'][0]);
//    $selections = _views_bulk_operations_get_selection($vbo, $form_state);
    $selections = [];
    $selections[0] = 1;

    for ($i = 0; $i < count($form_state['build_info']['args'][0]->result); $i++) {
        //$selections[] = $form_state['build_info']['args'][0]->result[$i]->metsis_basket_iid;
    }
    $form = array();
    return $form;
}

function empty_action_submit($form, &$form_state) {
    //sdpm(get_metsis_db_records($form_state['build_info']['args'][0]->base_table, $form_state['selection'], array('metadata_identifier')));
    empty_metsis_db_records($form_state['build_info']['args'][0]->base_table);
//    $form_state['redirect'] = array('http://vg.no');
}

function empty_metsis_db_records($table) {
    global $user;
    db_delete($table)
        ->condition('uid', $user->uid)
        //need more condition to ensure that the user deletes correct records
        //we could give the user a form in the confirm stage, but that would
        //probably be unwelcome
        ->execute();
}

function empty_action_form_alter($from, $form_state, $form_id) {
    
}

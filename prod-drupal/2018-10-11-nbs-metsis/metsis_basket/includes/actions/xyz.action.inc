<?php

function metsis_basket_xyz_action_info() {
    return array('metsis_basket_xyz_my_custom_action' => array('type' => 'entity', 'label' => t('Add a user to Users field'), 'behavior' => array('changes_property'), 'configurable' => TRUE, 'vbo_configurable' => FALSE, 'triggers' => array('any'),),);
}

function metsis_basket_xyz_my_custom_action_form() {
    $form = array();
    $form['user'] = array('#type' => 'textfield', '#title' => t('User'), '#maxlength' => 60, '#autocomplete_path' => 'user/autocomplete', '#weight' => -1,);
    return $form;
}

function metsis_basket_xyz_my_custom_action_submit($form, &$form_state) {
    $personell_email = db_query('SELECT personell_email from {metsis_basket} WHERE metadata_identifier = :name', array(':name' => $form_state['values']['user']))->fetchField();
    return array('uid' => $uid,);
}

function metsis_basket_xyz_my_custom_action(&$entity, $context) {
    if (!isset($entity->field_users)) {
        return;
    } if (!isset($context['uid'])) {
        return;
    } if (!empty($entity->field_users)) {
        foreach ($entity->field_users[LANGUAGE_NONE] as $ref) {
            if ($ref['target_id'] === $context['uid']) {
                return;
            }
        }
    } $user = array('target_id' => $context['uid'],);
    if (!empty($entity->field_users)) {
        $entity->field_users[LANGUAGE_NONE][] = $user;
        return;
    } $entity->field_users[LANGUAGE_NONE] = array($user);
}

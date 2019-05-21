<?php

function firstmodule_form() {
    return drupal_get_form('firstmodule_form1');
}

function firstmodule_form1($form_state) {
    $form['name'] = array('#type' => 'textfield', '#title' => t('First Name'),);
    $form['submit'] = array('#type' => 'submit', '#value' => 'Submit',);
    return $form;
}

function firstmodule_form1_submit($form, &$form_state) {
    drupal_set_message(t('The form has been submitted.'));
}
<?php

function firstmodule_form() {
  return \Drupal::formBuilder()->getForm('firstmodule_form1');
}

function firstmodule_form1($form_state) {
  $form['name'] = array('#type' => 'textfield', '#title' => t('First Name'),);
  $form['submit'] = array('#type' => 'submit', '#value' => 'Submit',);
  return $form;
}

function firstmodule_form1_submit($form, &$form_state) {
  \Drupal::messenger()->addMessage(t('The form has been submitted.'));
}

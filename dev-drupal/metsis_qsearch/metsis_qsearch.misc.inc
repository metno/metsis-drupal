<?php

function firstmodule_form() {
    return drupal_get_form('firstmodule_form1');
}

# our drupal form "form builder" function

function firstmodule_form1($form_state) {

    # an html textfield for our drupal form
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('First Name'),
    );

    # an html submit button for our drupal form
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Submit',
    );
    return $form;
}

# our drupal form submit handler (using a submit handler
# given by the default naming convention, i.e.,
# 'firstmodule_form1' plus '_submit')

function firstmodule_form1_submit($form, &$form_state) {
    drupal_set_message(t('The form has been submitted.'));
}

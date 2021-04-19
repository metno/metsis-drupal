<?php

function mcsv_var_select_validate($form, &$form_state) {
  if (!array_filter($form_state->getValue('selected_variables'))) {
    $form_state->setErrorByName('', t('You must choose atleast one variable!'));
  }
}

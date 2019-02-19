<?php

/**
 * mcsv_var_select_validate{
 */
function mcsv_var_select_validate($form, &$form_state) {
    if (!array_filter($form_state['values']['selected_variables'])) {
        form_set_error('', t('You must choose atleast one variable!'));
    }
}
    /**
     * mcsv_var_select_validate}
     */
    
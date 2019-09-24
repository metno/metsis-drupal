<?php

function metsis_basket_misc_action_info() {
  return array('metsis_basket_misc_action' => array('type' => 'node', 'label' => t('Misc action'), 'behavior' => array('changes_property'), 'configurable' => FALSE, 'vbo_configurable' => TRUE, 'triggers' => array('any'),),);
}

function metsis_basket_misc_action_form($options) {
  $form = array();
  $form['dataset'] = array('#type' => 'select', '#title' => t('Choose your dataset'), '#options' => array('Normap' => t('Normap'), 'SIOS' => t('SIOS'),), '#default_value' => !empty($options['dataset']) ? $options['dataset'] : '',);
  return $form;
}

<?php

function metsis_basket_delete_action_info() {
  $action = array('metsis_basket_delete_action' => array('type' => 'entity', 'label' => t('ADC delete items'), 'behavior' => array('changes_property'), 'configurable' => FALSE, 'vbo_configurable' => FALSE, 'pass rows' => TRUE, 'triggers' => array('any'), 'aggregate' => TRUE,),);
  return $action;
}

function metsis_basket_delete_action(&$node, $context) {
  global $user;
  $table = "metsis_basket";
  $records = array_keys($node);
  foreach ($records as $r) {
    db_delete($table)->condition('iid', $r)->condition('uid', $user->uid)->execute();
  }
}

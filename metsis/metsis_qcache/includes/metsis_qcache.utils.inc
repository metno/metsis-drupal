<?php

function mq_get_fields($form_state_values, $query_id, $session_id) {
  $fields = array();
  $fields['session_id'] = $session_id;
  $fields['query_id'] = $query_id;
  $fields['query'] = json_encode($form_state_values);
  $fields['drupal_form_id'] = $form_state_values['form_id'];
  $fields['qcache_timestamp'] = time();
  return $fields;
}

function mq_db_insert($fields) {
  $query = db_insert('metsis_qcache')->fields($fields)->execute();
}

function mq_db_select($query_id) {
  $q = "select query from {metsis_qcache} WHERE query_id = :query_id";
  $result = db_query($q, array(':query_id' => $query_id))->fetchObject();
  return json_decode($result->query);
}

<?php

function metsis_cache_views_data() {
  $data = array();
  $data['metsis_cache']['table']['group'] = t('metsis_cache');
  $data['metsis_cache']['table']['base'] = array('title' => t('metsis_cache'), 'help' => t('Contains cache records we to make available in Views.'),);
  $data['metsis_cache']['table']['join'] = array('node' => array('left_field' => 'nid', 'field' => 'node_id',),);
  $data['metsis_cache']['id'] = array('title' => t('ID'), 'help' => t('The record ID.'), 'field' => array('handler' => 'views_handler_field_numeric',), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_numeric',),);
  $data['metsis_cache']['name'] = array('title' => t('Name'), 'help' => t('The record name.'), 'field' => array('handler' => 'views_handler_field',), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
  $data['metsis_cache']['deadline'] = array('title' => t('Deadline'), 'help' => t('The record deadline.'), 'field' => array('handler' => 'views_handler_field_date',), 'sort' => array('handler' => 'views_handler_sort_date',), 'filter' => array('handler' => 'views_handler_filter_date',),);
  $data['metsis_cache']['node_id'] = array('title' => t('Node ID'), 'help' => t('The record node ID.'), 'field' => array('handler' => 'views_handler_field_node',), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_numeric',), 'relationship' => array('base' => 'node', 'field' => 'node_id', 'handler' => 'views_handler_relationship', 'label' => t('Node'),), 'argument' => array('handler' => 'views_handler_argument_node_nid', 'numeric' => TRUE, 'validate type' => 'nid',),);
  return $data;
}

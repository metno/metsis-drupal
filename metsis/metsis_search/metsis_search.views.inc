<?php

/**
 * @file
 * Views specific hooks.
 */

/**
 * Implements hook_views_data().
 */
function metsis_search_views_data() {
  $data['views']['metsis_search_parent_info'] = [
    'title' => t('Show info about parent'),
    'help' => t('Render info about the parent dataset.'),
    'area' => [
      'id' => 'metsis_search_parent_info',
    ],
  ];
  return $data;
}

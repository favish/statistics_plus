<?php

/**
 * @file
 * Provide views data for statistics_plus.module.
 */

/**
 * Implements hook_views_data().
 */
function statistics_plus_views_data() {
  $data['node_counter']['table']['group']  = t('Content statistics plus');

  $data['node_counter']['table']['provider'] = 'statistics';

  $data['node_counter']['table']['join'] = [
    'node_field_data' => [
      'left_field' => 'nid',
      'field' => 'nid',
    ],
  ];

  $data['node_counter']['totalfuzzcount'] = [
    'title' => t('Total views with fuzz'),
    'help' => t('The total number of times the node has been viewed with fuzz.'),
    'field' => [
      'id' => 'statistics_numeric',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  return $data;
}

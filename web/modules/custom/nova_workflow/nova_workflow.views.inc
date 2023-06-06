<?php

/**
 * @file
 * The module file.
 */

/**
 * Implements hook_views_data().
 */
function nova_workflow_views_data() {
  $data['views']['table']['group'] = t('Custom Global');
  $data['views']['table']['join'] = [
    // #global is a special flag which allows a table to appear all the time.
    '#global' => [],
  ];

  $data['views']['nova_views_field'] = [
    'title' => t('Nova'),
    'help' => t('Nova wizard terms.'),
    'field' => [
      'id' => 'nova_views_field',
    ],
  ];
  return $data;
}

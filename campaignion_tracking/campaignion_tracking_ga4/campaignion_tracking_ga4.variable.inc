<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_tracking_ga4_variable_info($options) {
  $v['campaignion_tracking_ga4_id'] = [
    'title' => t('Google Analytics ID'),
    'description' => t('If an ID is set here the GA snippet for this tag is generated automatically.'),
    'type' => 'string',
    'default' => '',
    'localize' => FALSE,
  ];
  return $v;
}

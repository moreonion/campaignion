<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_m2t_send_variable_info($options) {
  $v['campaignion_m2t_send_enabled_nodes'] = [
    'title' => t('Enabled nodes'),
    'description' => t('Only send emails for these nodes.'),
    'type' => 'unknown',
    'default' => [],
  ];
  return $v;
}

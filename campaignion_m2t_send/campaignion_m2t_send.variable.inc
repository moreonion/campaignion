<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_m2t_send_variable_info($options) {
  $v['campaignion_m2t_send_content_types'] = [
    'title' => t('Restrict to content-types'),
    'description' => t('Only process actions of these types.'),
    'type' => 'options',
    'default' => ['match_to_target'],
    'localize' => FALSE,
    'options callback' => 'node_type_get_names',
    'multiple' => TRUE,
  ];
  return $v;
}

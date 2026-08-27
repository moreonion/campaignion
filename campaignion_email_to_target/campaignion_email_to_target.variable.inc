<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_email_to_target_variable_info($options) {
  $v['campaignion_email_to_target_e2t_api_url'] = [
    'title' => t('Auth app URL'),
    'description' => t('URL for the e2t-api excluding the version prefix.'),
    'type' => 'url',
    'default' => '',
    'localize' => FALSE,
  ];
  $v['campaignion_email_to_target_mp_datasets'] = [
    'title' => t('MP datasets'),
    'description' => t('List of dataset keys for all datasets that are considered MP datasets'),
    'type' => 'array',
    'default' => ['mp'],
    'localize' => FALSE,
  ];
  return $v;
}

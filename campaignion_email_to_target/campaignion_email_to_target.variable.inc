<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_email_to_target_variable_info($options) {
  $v['campaignion_email_to_target_api_key'] = [
    'title' => t('API key'),
    'description' => t('Public & secret key for authenticating against backend apps.'),
    'type' => 'properties',
    'default' => [],
    'localize' => FALSE,
  ];
  $v['campaignion_email_to_target_auth_app_url'] = [
    'title' => t('Auth app URL'),
    'description' => t('URL for the auth app excluding the version prefix.'),
    'type' => 'url',
    'default' => '',
    'localize' => FALSE,
  ];
  $v['campaignion_email_to_target_credentials'] = [
    'title' => t('e2t api credentials'),
    'description' => t('API endpoint and API keys for the e2t-api service.'),
    'type' => 'properties',
    'default' => [],
    'localize' => FALSE,
  ];
  return $v;
}

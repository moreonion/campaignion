<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function impact_stack_auth_variable_info($options) {
  $v['impact_stack_auth_api_key'] = [
    'title' => t('API key'),
    'description' => t('Public & secret key for authenticating against backend apps.'),
    'type' => 'properties',
    'default' => [],
    'localize' => FALSE,
  ];
  $v['impact_stack_auth_app_url'] = [
    'title' => t('Auth app URL'),
    'description' => t('URL for the auth app excluding the version prefix.'),
    'type' => 'url',
    'default' => '',
    'localize' => FALSE,
  ];
  return $v;
}

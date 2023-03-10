<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_tracking_gtm_variable_info($options) {
  $v['campaignion_tracking_gtm_id'] = [
    'title' => t('Google Tag Manager container ID'),
    'description' => t('If an ID is set here the GTM snippet for this tag is generated automatically.'),
    'type' => 'string',
    'default' => '',
    'localize' => FALSE,
  ];
  return $v;
}

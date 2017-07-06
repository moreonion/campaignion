<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_newsletters_variable_info($options) {
  $v['campaignion_newsletters_batch_size'] = [
    'title' => t('Send queue batch size', [], $options),
    'description' => t('The number of subscriptions that are sent to the newsletter provider in one batch.', [], $options),
    'type' => 'number',
    'default' => 50,
    'localize' => FALSE,
  ];
  $v['campaignion_newsletters_poll_time'] = [
    'title' => t('Polling duration (seconds)', [], $options),
    'description' => t('The amount of time spent for polling newsletter provider data each cron run.', [], $options),
    'type' => 'number',
    'default' => 5,
    'localize' => FALSE,
  ];
  return $v;
}

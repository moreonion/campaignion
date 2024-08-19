<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_m2t_send_variable_info($options) {
  $v['campaignion_m2t_send_cron_time_limit'] = [
    'title' => t('Time limit for M2T send cron-jobs'),
    'description' => t('When a m2t send cron-job has been running for more than this amount of seconds no new batch will be started during this cron-run.'),
    'type' => 'number',
    'default' => 20,
    'localize' => FALSE,
  ];
  $v['campaignion_m2t_send_enabled_nodes'] = [
    'title' => t('Enabled nodes'),
    'description' => t('Only send emails for these nodes.'),
    'type' => 'unknown',
    'default' => [],
  ];
  return $v;
}

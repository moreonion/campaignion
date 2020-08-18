<?php

/**
 * @file
 * Document hooks invoked by this module.
 */

/**
 * Implements hook_campaignion_layout_info().
 */
function hook_campaignion_layout_info() {
  $info['2col'] = [
    'title' => t('Two-column layout'),
  ];
  return $info;
}

/**
 * Implements hook_campaignion_layout_info_alter().
 */
function hook_campaignion_layout_info_alter(&$info) {
}

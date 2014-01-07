<?php

/**
 * @file
 * Example implementations for all hooks that are
 * invoked by this module.
 */

/**
 * @return array
 *   class names indexed by (machine readable) content-type names
 */
function hook_campaignion_action_info() {
  $types['webform'] = 'Drupal\\campaignion\\Action\\FlexibleForm';
  return $types;
}

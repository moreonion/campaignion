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
  $types['webform'] = array(
    'class' => 'Drupal\\campaignion\\Action\\FlexibleForm',
    'parameters' => array(
      'thank_you_page' => array(
        'type' => 'thank_you_page',
        'reference' => 'field_thank_you_pages',
      ),
    ),
  );
  return $types;
}

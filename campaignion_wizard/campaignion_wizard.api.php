<?php

/**
 * @file
 * Document hooks invoked by this module.
 *
 * This file exists solely for documentation purposes. No code is executed.
 */

/**
 * Declare wizard plugins.
 *
 * @return array
 *   An array of class specs keyed by action type.
 */
function campaignion_wizard_info() {
  $info['default'] = '\\Drupal\\campaignion_wizard\\NodeWizard';
  return $info;
}

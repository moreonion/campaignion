<?php

/**
 * @file
 * Token hook implementations for campaignion_donation_amount_total.
 */

use Drupal\little_helpers\Webform\Submission;

/**
 * Implements hook_token_info().
 *
 * Provide a custom token for the total donation amount.
 */
function campaignion_donation_amount_total_token_info() {
  $info['tokens']['submission']['amount-total'] = [
    'name' => t('Donation amount total'),
    'description' => t('Calculates the sum of two donation amount fields'),
  ];
  return $info;
}

/**
 * Implements hook_tokens().
 */
function campaignion_donation_amount_total_tokens($type, $tokens, array $data = [], array $options = []) {
  // Return early unless submission tokens are needed and node and submission
  // are available.
  if ($type != 'submission' || empty($data['webform-submission']) || empty($data['node']) || !webform_variable_get('webform_token_access')) {
    return [];
  }

  $submission = new Submission($data['node'], $data['webform-submission']);
  $donation_amount_1 = $submission->valueByKey('donation_amount_1');
  $donation_amount_2 = $submission->valueByKey('donation_amount_2');

  $replacements = [];
  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'amount-total':
        $replacements[$original] = $donation_amount_1 + $donation_amount_2;
        break;
    }
  }
  return $replacements;
}

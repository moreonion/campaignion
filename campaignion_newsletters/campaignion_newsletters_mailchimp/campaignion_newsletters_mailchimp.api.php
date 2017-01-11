<?php

use \Drupal\campaignion_newsletters\Subscription;

/**
 * Alter data before sending it to MailChimp.
 */
function hook_campaignion_newsletters_mailchimp_data_alter(array &$data, Subscription $subscription) {
  $data['merge_fields']['SPECIAL'] = 'value';
  $data['interests']['hashid'] = TRUE;
}

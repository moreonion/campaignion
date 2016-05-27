<?php

use \Drupal\campaignion_newsletters\Subscription;

/**
 * Alter attributes before sending them to MailChimp.
 */
function hook_campaignion_newsletters_mailchimp_attributes_alter(array &$attributes, Subscription $subscription, $source) {
  $attributes['SPECIAL'] = 'value';
}

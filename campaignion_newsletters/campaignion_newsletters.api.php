<?php

/**
 * @file
 *
 * Hook documentation and examples. The code in this file is never actually
 * executed.
 */

use \Drupal\campaignion_newsletters\Subscription;

/**
 * Alter a newsletter subscription prior to being saved.
 */
function hook_campaignion_newsletters_subscription_presave(Subscription $subscription) {
  if ($subscription->email == 'just-for-testing@example.com') {
    $subscription->delete = TRUE;
  }
}

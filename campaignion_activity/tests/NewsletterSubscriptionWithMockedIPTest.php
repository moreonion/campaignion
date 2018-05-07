<?php

namespace Drupal\campaignion_activity;

use \Drupal\campaignion_newsletters\Subscription;

/**
 * Test newsletter subscription activities related to IP addresses.
 */

class NewsletterSubscriptionWithMockedIPTest extends \DrupalWebTestCase{

  // https://api.drupal.org/api/drupal/modules%21simpletest%21tests%21bootstrap.test/class/BootstrapIPAddressTestCase/7.x
  function setUp() {
    $this->oldserver = $_SERVER;
    $this->remote_ip = '127.1.0.1';
    drupal_static_reset('ip_address');
    $_SERVER['REMOTE_ADDR'] = $this->remote_ip;

    parent::setUp(['campaignion_activity', 'campaignion_newsletters']);

    db_delete('campaignion_activity')->execute();
    db_delete('campaignion_activity_newsletter_subscription')->execute();
  }

  function tearDown() {
    $_SERVER = $this->oldserver;
    drupal_static_reset('ip_address');

    parent::tearDown();

    db_delete('campaignion_newsletters_subscriptions')->execute();
    db_delete('campaignion_newsletters_queue')->execute();
    db_delete('campaignion_activity')->execute();
    db_delete('campaignion_activity_newsletter_subscription')->execute();
  }

  public function test_remote_addr() {
    $email = 'bydataduplicate@test.com';
    $list_id = 4711;
    $s = Subscription::byData($list_id, $email);
    $s->save();

    $subscription = NewsletterSubscription::fromSubscription($s, 'subscribe', FALSE);
    $this->assertEqual('127.1.0.1', $subscription->remote_addr);
  }

}

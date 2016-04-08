<?php

namespace Drupal\campaignion_newsletters;

use \Drupal\campaignion\Contact;

class TestSubscription extends Subscription {
  public $data = [];
  public function providerData() {
    return [$this->data, sha1(serialize($this->data))];
  }
}

class QueueTest extends \DrupalWebTestCase {
  function test_updateContactWhileCronIsRunnning() {
    TestSubscription::fromData(4711, 't@e.org')->save();
    $items = QueueItem::claimOldest(2);
    $this->assertCount(1, $items);

    $s = TestSubscription::fromData(4711, 't@e.org');
    $s->data = ['test' => '1'];
    $s->save();

    foreach ($items as $item) {
      $item->delete();
    }

    $items = QueueItem::claimOldest(2);
    $this->assertCount(1, $items, 'New data failed to override old (but claimed) data.');
  }

  function tearDown() {
    db_delete('campaignion_newsletters_subscriptions')->execute();
    db_delete('campaignion_newsletters_queue')->execute();
  }
}

<?php

namespace Drupal\campaignion_newsletters_cleverreach;

use \Drupal\campaignion_newsletters\NewsletterList;
use \Drupal\campaignion_newsletters\QueueItem;

/**
 * Test the CleverReach API implementation.
 */
class CleverReachTest extends \DrupalUnitTestCase {

  /**
   * Test that subscribe() does not pass 'registered' when updating subscribers.
   */
  public function testSubscribeUpdateNoRegisteredDate() {
    $api = $this->getMockBuilder(ApiClient::class)
      ->setMethods([
        'receiverGetByEmail',
        'receiverAdd',
        'receiverUpdate',
        'formsSendActivationMail',
      ])
      ->disableOriginalConstructor()
      ->getMock();
    $cr = new CleverReach($api, 'test');

    $result = (object) [
      'status' => 'SUCCESS',
      'data' => [],
    ];
    $item = new QueueItem(['created' => 42]);
    $list = new NewsletterList(['data' => (object) ['id' => 42]]);
    $api->expects($this->once())->method('receiverUpdate')
      ->with($this->anything(), $this->equalTo([
        'email' => $item->email,
        'attributes' => NULL,
        'active' => TRUE,
        'activated' => 42,
      ]))->willReturn($result);
    $api->method('receiverGetByEmail')
      ->willReturn((object) ['message' => 'found']);
    $cr->subscribe($list, $item);
  }

}

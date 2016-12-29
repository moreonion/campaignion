<?php

namespace Drupal\campaignion_newsletters_mailchimp;

use \Drupal\campaignion_newsletters\NewsletterList;
use \Drupal\campaignion_newsletters\QueueItem;

class MailChimpTest extends \DrupalUnitTestCase {

  public function test_key2dc_validKey() {
    $this->assertEquals('us12', MailChimp::key2dc('testkey-us12'));
  }

  protected function mockChimp($methods = []) {
    $methods[] = 'send';
    $api = $this->getMockBuilder(Rest\MailChimpClient::class)
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();
    return [$api, new MailChimp($api, 'testname')];
  }

  public function test_getLists_noLists() {
    list($api, $provider) = $this->mockChimp();
    $api->expects($this->once())->method('send')->willReturn(['lists' => []]);
    $this->assertEquals([], $provider->getLists());
  }

  public function test_getLists_oneList() {
    list($api, $provider) = $this->mockChimp();
    $paging = ['offset' => 0, 'count' => 100];
    $list = ['id' => 'a1234', 'name' => 'mocknewsletters'];
    $list_query = ['fields' => 'lists.id,lists.name'] + $paging;
    $merge_query = ['fields' => 'merge_fields.tag'] + $paging;
    $webhook_query = ['fields' => 'webhooks.url'] + $paging;
    $api->expects($this->exactly(4))->method('send')->withConsecutive(
      [$this->equalTo('/lists'), $this->equalTo($list_query)],
      [$this->equalTo('/lists/a1234/merge-fields'), $this->equalTo($merge_query)],
      [$this->equalTo('/lists/a1234/webhooks'), $this->equalTo($webhook_query)],
      [$this->equalTo('/lists/a1234/webhooks')]
    )->will($this->onConsecutiveCalls(
      ['lists' => [$list], 'total_items' => 1],
      ['merge_fields' => [], 'total_items' => 0],
      ['webhooks' => [], 'total_items' => 0],
      $this->throwException(Rest\ApiError::fromHttpError(new Rest\HttpError((object) [
        'code' => 400,
        'status_message' => 'Bad Request',
        'data' => json_encode(['title' => '', 'detail' => '', 'errors' => []]),
      ]), 'POST', '/lists/a1234/webhooks'))
    ));
    $this->assertEquals([NewsletterList::fromData([
      'identifier' => $list['id'],
      'title'      => $list['name'],
      'source'     => 'MailChimp-testname',
      'data'       => (object) ($list + ['merge_vars' => []]),
    ])], $provider->getLists());
  }

  public function test_subscribe_newContact() {
    $list = ['id' => 'a1234', 'name' => 'mocknewsletters'];
    $list_o = NewsletterList::fromData([
      'identifier' => $list['id'],
      'title'      => $list['name'],
      'source'     => 'MailChimp-testname',
      'data'       => (object) ($list + ['merge_vars' => []]),
    ]);
    list($api, $provider) = $this->mockChimp();
    $item = new QueueItem([
      'args' => ['send_optin' => FALSE],
      'data' => ['FNAME' => 'Test', 'LNAME' => 'Test'],
    ]);
    $provider->subscribe($list_o, $item);
  }

  public function test_unsubscribe_nonExisting() {
    $list = ['id' => 'a1234', 'name' => 'mocknewsletters'];
    $list_o = NewsletterList::fromData([
      'identifier' => $list['id'],
      'title'      => $list['name'],
      'source'     => 'MailChimp-testname',
      'data'       => (object) ($list + ['merge_vars' => []]),
    ]);
    list($api, $provider) = $this->mockChimp(['put']);
    $item = new QueueItem([
      'email' => 'test@example.com',
    ]);
    $hash = md5(strtolower($item->email));

    $api->expects($this->once())->method('put')->with(
      $this->equalTo("/lists/a1234/members/$hash"),
      $this->anything(),
      $this->equalTo(['status' => 'unsubscribed'])
    )->will($this->throwException(Rest\ApiError::fromHttpError(new Rest\HttpError((object) [
      'code' => 404,
      'status_message' => 'Resource not found',
      'data' => json_encode(['title' => 'Resource not found', 'detail' => '', 'errors' => []]),
    ]), 'DELETE', "/lists/a1234/members/$hash")));
    $provider->unsubscribe($list_o, $item);
  }

}


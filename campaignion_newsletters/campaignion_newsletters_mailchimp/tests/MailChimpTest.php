<?php

namespace Drupal\campaignion_newsletters_mailchimp;

use \Drupal\campaignion_newsletters\NewsletterList;
use \Drupal\campaignion_newsletters\QueueItem;

use \Drupal\campaignion_newsletters_mailchimp\Rest\ApiError;
use \Drupal\campaignion_newsletters_mailchimp\Rest\HttpError;

/**
 * Test the MailChimp newsletter provider.
 */
class MailChimpTest extends \DrupalUnitTestCase {

  public function test_key2dc_validKey() {
    $this->assertEquals('us12', MailChimp::key2dc('testkey-us12'));
  }

  /**
   * Generate a MailChimpClient instance with a stubbed out send method.
   *
   * @param string[] $methods
   *   Other methods to stub out.
   *
   * @return array
   *   An array with two elements:
   *     1. The stubbed API.
   *     2. A MailChimp instance using this API.
   */
  protected function mockChimp(array $methods = []) {
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
    $list_query = ['fields' => 'lists.id,lists.name,total_items'] + $paging;
    $merge_query = ['fields' => 'merge_fields.tag,total_items'] + $paging;
    $webhook_query = ['fields' => 'webhooks.id,webhooks.url,total_items'] + $paging;
    $api->expects($this->exactly(5))->method('send')->withConsecutive(
      [$this->equalTo('/lists'), $this->equalTo($list_query)],
      [$this->equalTo('/lists/a1234/merge-fields'), $this->equalTo($merge_query)],
      [$this->equalTo('/lists/a1234/interest-categories')],
      [$this->equalTo('/lists/a1234/webhooks'), $this->equalTo($webhook_query)],
      [$this->equalTo('/lists/a1234/webhooks')]
    )->will($this->onConsecutiveCalls(
      ['lists' => [$list], 'total_items' => 1],
      ['merge_fields' => [], 'total_items' => 0],
      ['categories' => [], 'total_items' => 0],
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
      'data'       => (object) ($list + ['merge_vars' => [], 'groups' => []]),
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
    )->will($this->throwException(ApiError::fromHttpError(new HttpError((object) [
      'code' => 404,
      'status_message' => 'Resource not found',
      'data' => json_encode(['title' => 'Resource not found', 'detail' => '', 'errors' => []]),
    ]), 'DELETE', "/lists/a1234/members/$hash")));
    $provider->unsubscribe($list_o, $item);
  }

  public function test_getInterestGroups_docExample() {
    $list_id = '57afe96172';
    $category_id = 'a1e9f4b7f6';
    list($api, $provider) = $this->mockChimp(['get']);

    $api->expects($this->exactly(2))->method('get')->withConsecutive(
      [$this->equalTo("/lists/$list_id/interest-categories")],
      [$this->equalTo("/lists/$list_id/interest-categories/$category_id/interests")]
    )->will($this->onConsecutiveCalls(
      ['categories' => [['id' => $category_id]], 'total_items' => 1],
      ['interests' => [
        ['id' => "9143cf3bd1", 'name' => "Sometimes you just gotta 'spress yourself."],
        ['id' => "3a2a927344", 'name' => "I'm just a poor boy from a poor family."],
        ['id' => "f9c8f5f0ff", 'name' => "What's with all these cute kittens?"],
        ['id' => "f231b09abc", 'name' => "I knock your socks off with my beat box."],
        ['id' => "bd6e66465f", 'name' => "Two chimps walk into a bar. The other chimp ducks."],
      ], 'total_items' => 5]
    ));

    $groups = $provider->getInterestGroups($list_id);
    $this->assertCount(5, $groups);
    $this->assertArrayHasKey('bd6e66465f', $groups);
  }

  /**
   * Test that obsolete webhooks are deleted when updating webhooks for a list.
   */
  public function testSetWebhooksDeletesOldWebhooks() {
    $list = ['id' => 'a1234', 'name' => 'mocknewsletters'];
    $list_o = NewsletterList::fromData([
      'identifier' => $list['id'],
      'title'      => $list['name'],
      'source'     => 'MailChimp-testname',
      'data'       => (object) ($list + ['merge_vars' => []]),
    ]);
    list($api, $provider) = $this->mockChimp(['post', 'delete', 'getPaged']);

    $api->expects($this->once())->method('getPaged')->with(
      $this->equalTo('/lists/a1234/webhooks')
    )->will($this->returnValue([
      ['url' => $GLOBALS['base_url'] . '/old-webhook', 'id' => 'oldhook'],
    ]));
    $api->expects($this->once())->method('delete')->with(
      $this->equalTo('/lists/a1234/webhooks/oldhook')
    );

    $provider->setWebhooks([$list_o]);
  }

}


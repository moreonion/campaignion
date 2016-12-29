<?php
/**
 * @file
 * implements NewsletterProvider using MailChimps API.
 *
 * See http://apidocs.mailchimp.com/ for documentation.
 */

namespace Drupal\campaignion_newsletters_mailchimp;

use \Drupal\campaignion_newsletters\NewsletterList;
use \Drupal\campaignion_newsletters\ProviderBase;
use \Drupal\campaignion_newsletters\QueueItem;
use \Drupal\campaignion_newsletters\Subscription;

class MailChimp extends ProviderBase {

  protected $account;
  protected $api;

  public static function key2dc($key) {
    return substr($key, strrpos($key, '-') + 1);
  }

  public static function fromParameters(array $params) {
    $dc = static::key2dc($params['key']);
    $endpoint = "https://campaignion:{$params['key']}@{$dc}.api.mailchimp.com/3.0";
    return new static(new Rest\MailChimpClient($endpoint), $params['name']);
  }

  /**
   * Constructor. Gets settings and fetches intial group list.
   */
  public function __construct($api, $name) {
    $this->api = $api;
    $this->account = $name;
  }

  /**
   * Fetches current lists from the provider.
   *
   * @return array
   *   An array of associative array
   *   (properties: identifier, title, source, language).
   */
  public function getLists() {
    $mc_lists = $this->api->getPaged('/lists', ['fields' => 'lists.id,lists.name'], [], 100);
    $this->merge_vars = array();
    $lists = array();
    foreach ($mc_lists as $list) {
      $v = $this->api->getPaged("/lists/{$list['id']}/merge-fields", ['fields' => 'merge_fields.tag'], [], 100);
      // @TODO also get interest groups.
      $list['merge_vars'] = $v ? $v : array();
      $lists[] = NewsletterList::fromData([
        'identifier' => $list['id'],
        'title'      => $list['name'],
        'source'     => 'MailChimp-' . $this->account,
        'data'       => (object) $list,
      ]);
    }

    // Try refreshing the webhooks. This will fail on test installations with
    // non-routable addresses (ie. for testing environments) - which is fine.
    try {
      $this->setWebhooks($lists);
    }
    catch (Rest\ApiError $e) {
      watchdog_exception('campaignion_newsletters_mailchimp', $e);
    }

    return $lists;
  }

  /**
   * Register webhooks for a set of lists (if needed).
   *
   * @param \Drupal\campaignion_newsletters\NewsletterList[] $lists
   *   Register webhooks for these $lists.
   * @throws \Drupal\campaignion_newsletters_mailchimp\Rest\ApiError
   */
  protected function setWebhooks($lists) {
    $webhook_url = $GLOBALS['base_url'] . '/'
      . CAMPAIGNION_NEWSLETTERS_MAILCHIMP_WEBHOOK_URL;

    foreach ($lists as $list) {
      // Get existing webhook URLs.
      $webhook_urls = [];
      foreach ($this->api->getPaged("/lists/{$list->identifier}/webhooks", ['fields' => 'webhooks.url'], [], 100) as $webhook) {
        $webhook_urls[$webhook['url']] = TRUE;
      }
      if (!isset($webhook_urls[$webhook_url])) {
        $this->api->post("/lists/{$list->identifier}/webhooks", [], [
          'url' => $webhook_url,
          'events' => [
            'subscribe' => FALSE,
            'unsubscribe' => TRUE,
            'profile' => FALSE,
            'cleaned' => TRUE,
            'campaign' => FALSE,
          ],
          'sources' => [
            'user' => TRUE,
            'admin' => TRUE,
            'api' => TRUE,
          ],
        ]);
      }
    }
  }

  /**
   * Fetches current lists of subscribers from the provider.
   *
   * @return array
   *   an array of subscribers.
   */
  public function getSubscribers($list) {
    $page = 0;
    $receivers = array();
    $list_id = $list->data->id;

    foreach ($this->api->getPaged("/lists/{$list_id}/members", [
      'status' => 'subscribed',
      'fields' => 'members.email',
    ], [], 1000) as $new_receiver) {
      $receivers[] = $new_receiver['email'];
    }
    return $receivers;
  }

  /**
   * Get values for all merge tags if possible.
   */
  protected function attributeData(Subscription $subscription) {
    $list = $subscription->newsletterList();
    $attributes = array();

    if ($source = $this->getSource($subscription, 'mailchimp')) {
      foreach ($list->data->merge_vars as $attribute) {
        $tag = $attribute['tag'];
        // MailChimp's merge tags are all upper-case. Our form-keys are usually
        // lowercase. Let's try both!
        if (($v = $source->value($tag)) || ($v = $source->value(strtolower($tag)))) {
          $attributes[$tag] = $v;
        }
      }
    }
    // Let other modules alter the attributes (ie. for adding groupings).
    drupal_alter('campaignion_newsletters_mailchimp_attributes', $attributes, $subscription, $source);
    return $attributes;
  }

  public function data(Subscription $subscription) {
    $data = $this->attributeData($subscription);
    $attr = $data;
    $fingerprint = sha1(serialize($attr));
    return array($data, $fingerprint);

  }

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   */
  public function subscribe(NewsletterList $list, QueueItem $item) {
    $this->api->post("/lists/{$list->identifier}/members", [], [
      'email_address' => $item->email,
      'status' => $item->optIt() ? 'pending' : 'subscribed',
      'merge_fields' => $item->data,
    ]);
  }

  /**
   * Unsubscribe a user, given a newsletter identifier and email address.
   *
   * Should ignore the request if there is no such subscription.
   */
  public function unsubscribe(NewsletterList $list, QueueItem $item) {
    $hash = md5(strtolower($item->email));
    $this->api->delete("/lists/{$list->identifier}/members/$hash");
  }

}

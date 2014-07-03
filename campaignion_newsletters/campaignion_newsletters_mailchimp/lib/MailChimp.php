<?php
/**
 * @file
 * implements NewsletterProvider using MailChimps API.
 *
 * See http://apidocs.mailchimp.com/ for documentation.
 */

namespace Drupal\campaignion_newsletters_mailchimp;

use \Drupal\campaignion\Contact;
use \Drupal\campaignion_newsletters\NewsletterList;
use \Drupal\campaignion_newsletters\Subscription;

class MailChimp implements \Drupal\campaignion_newsletters\NewsletterProviderInterface {
  protected $account;
  protected $key;
  protected $url;
  protected $api;
  /**
   * Constructor. Gets settings and fetches intial group list.
   */
  public function __construct(array $params) {
    libraries_load('mailchimp-api-php');

    $this->account = $params['name'];
    $this->key = $params['key'];

    $this->api = new \Mailchimp($this->key);

  }

  /**
   * Fetches current lists from the provider.
   *
   * @return array
   *   An array of associative array
   *   (properties: identifier, title, source, language).
   */
  public function getLists() {
    $mc_lists = $this->call('getList', array());
    $this->merge_vars = array();
    $lists = array();
    foreach ($mc_lists as $list) {
      $lists[] = NewsletterList::fromData(
        array(
          'identifier' => $list['id'],
          'title'      => $list['name'],
          'source'     => 'MailChimp-' . $this->account,
          'data'       => (object) $list,
          // @TODO: find a way to get an actual list specific language.
        ));

      $webhook_url = $GLOBALS['base_url'] . '/' .
        CAMPAIGNION_NEWSLETTERS_MAILCHIMP_WEBHOOK_URL;
      $webhook_urls = array();
      foreach ($this->api->lists->webhooks($list['id']) as $webhook) {
        $webhook_urls[] = $webhook['url'];
      }
      if (!in_array($webhook_url, $webhook_urls)) {
        $this->call('webhookAdd', $list['id'], $webhook_url);
      }
    }
    return $lists;
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

    do {
      $new_receivers = $this->call('members', $list_id, 'subscribed',
                array(
                  'start' => $page++,
                  'limit' => 100,
                ));
      foreach ($new_receivers as $new_receiver) {
        $receivers[] = $new_receiver['email'];
      }
    } while ($new_receivers);
    return $receivers;
  }

  protected function attributeData(Subscription $subscription) {
    static $merge_vars = array();
    $list = $subscription->newsletterList();
    $attributes = array();

    if (!isset($merge_vars[$list->identifier])) {
      $merge_vars[$list->identifier] = $this->call(
        'mergeVars',
        array($list->identifier))[0]['merge_vars'];
    }

    if ($contact = Contact::byEmail($subscription->email)) {
      $exporter = new CampaignionContactExporter($contact);

      foreach ($merge_vars[$list->identifier] as $attribute) {
        if ($value = $exporter->value($attribute['tag'])) {
          $attributes[$attribute['tag']] = $value;
        }
      }
    }
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
   *
   * @return: True on success.
   */
  public function subscribe($list, $mail, $data) {
    $this->call('subscribe',
      $list->identifier,
      array('email' => $mail),
      $data,
      'html',
      false,
      true,
      false,
      true
    );
    return true;
  }

  /**
   * Unsubscribe a user, given a newsletter identifier and email address.
   *
   * Should ignore the request if there is no such subscription.
   *
   * @return: True on success.
   */
  public function unsubscribe($list, $mail) {
    try {
      $this->call('unsubscribe',
        $list->identifier,
        array('email' => $mail)
      );
    }
    catch (\Mailchimp_Email_NotExists $e) { return true; }
    return true;
  }

  /**
   * Wraps Mailchimp API calls to deal with it's results and errors.
   */
  protected function call($function) {
    $arguments = func_get_args();
    array_shift($arguments);
    $result = array('data' => NULL);
    try {
      $result = call_user_func_array(
        array($this->api->lists, $function),
        $arguments
      );
    }
    catch(\Mailchimp_Error $e) {
      watchdog('MailChimp', 'Mailchimp API Exception: "' . $e->getMessage() . '"', NULL, WATCHDOG_INFO);
    }
    if (!empty($result['errors'])) {
      foreach ($result['errors'] as $error) {
        watchdog('MailChimp', $error);
        return false;
      }
    }
    else {
      return $result['data'];
    }
  }

  /**
   * Protected clone method to prevent cloning of the singleton instance.
   */
  protected function __clone() {}

  /**
   * Protected unserialize method to prevent unserializing of singleton.
   */
  protected function __wakeup() {}
}

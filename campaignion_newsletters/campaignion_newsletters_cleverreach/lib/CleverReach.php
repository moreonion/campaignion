<?php
/**
 * @file
 * implements NewsletterProvider using CleverReach API.
 *
 * See http://api.cleverreach.com/soap/doc/5.0/ for documentation.
 */

namespace Drupal\campaignion_newsletters_cleverreach;

class CleverReach implements \Drupal\campaignion_newsletters\NewsletterProviderInterface {
  protected $account;
  protected $key;
  protected $url;
  protected $api;
  protected $groups;
  /**
   * Constructor. Gets settings and fetches intial group list.
   */
  public function __construct($account, $key) {
    $this->account = $account;
    $this->key = $key;

    $url = variable_get('cleverreach_wsdl_url');
    $this->api = new \SoapClient($url);

    $this->groups = $this->listGroups();
  }

  /**
   * Fetches current lists from the provider.
   *
   * @return array
   *   An array of associative array
   *   (properties: identifier, title, source, language).
   */
  public function getLists() {
    $lists = array();
    foreach ($this->groups as $group) {
      $details = $this->getGroupDetails($group);
      $id = $this->toIdentifier($details->name);
      $lists[] = array(
        'identifier' => $id,
        'title'      => $details->name,
        'source'     => 'CleverReach-' . $this->account,
        // @TODO: find a way to get an actual list specific language.
        'language'   => language_default('language'),
      );
    }
    return $lists;
  }

  /**
   * Returns TRUE if the current providers manages the given list.
   */
  public function hasList($list) {
    return array_key_exists($list, $this->groups);
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

    if (empty($this->groups[$list])) {
      return $receivers;
    }
    $group_id = $this->groups[$list]->id;

    do {
      $result = $this->api->receiverGetPage($this->key, $group_id,
                array(
                  'page'   => $page++,
                  'filter' => 'active',
                ));
      if ($result->message == 'data not found') {
        return $receivers;
      }
      else {
        $new_receivers = $this->handleResult($result);
        foreach ($new_receivers as $new_receiver) {
          $receivers[] = $new_receiver->email;
        }
      }
    } while ($new_receivers);
    return $receivers;
  }

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   *
   * @return: True on success.
   */
  public function subscribe($newsletter, $mail) {
    $user = array(
      'email'  => $mail,
      'active' => TRUE,
    );
    $group_id = $this->groups[$newsletter]->id;
    $result = $this->api->receiverGetByEmail($this->key, $group_id, $mail, 0);
    if ($result->message === 'data not found') {
      $result = $this->api->receiverAdd($this->key, $group_id, $user);
    }
    else {
      $result = $this->api->receiverUpdate($this->key, $group_id, $user);
    }
    return (bool) $this->handleResult($result);
  }

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   *
   * Should ignore the request if there is no such subscription.
   *
   * @return: True on success.
   */
  public function unsubscribe($newsletter, $mail) {
    $group_id = $this->groups[$newsletter]->id;
    $result = $this->api->receiverDelete($this->key, $group_id, $mail);
    return (bool) $this->handleResult($result);
  }

  /**
   * Fetches a list of groups (without details). Called by the constructor.
   */
  protected function listGroups() {
    $data = $this->handleResult($this->api->groupGetList($this->key));
    $return = array();
    if ($data !== FALSE) {
      foreach ($data as $group) {
        $identifier = $this->toIdentifier($group->name);
        $return[$identifier] = $group;
      }
      return $return;
    }
    else {
      return array();
    }
  }

  /**
   * Fetches details for a single, given group.
   */
  protected function getGroupDetails($group) {
    $result = $this->api->groupGetDetails($this->key, $group->id);
    return $this->handleResult($result);
  }

  /**
   * Handles errors if any, extracts data if not.
   */
  protected function handleResult($result) {
    if ($result->status !== 'SUCCESS') {
      watchdog('CleverReach', '@status #@code: @message', array(
          '@status' => $result->status,
          '@code' => $result->statuscode,
          '@message' => $result->message,
        ),
        WATCHDOG_ERROR);
      return FALSE;
    }
    else {
      return $result->data;
    }
  }

  /**
   * Helper to create unified identifiers for newsletters.
   */
  public function toIdentifier($string) {
    return strtolower(drupal_clean_css_identifier($string));
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

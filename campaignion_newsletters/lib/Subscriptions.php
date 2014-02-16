<?php

namespace Drupal\campaignion_newsletters;

class Subscriptions {
  protected $subscriptions;

  protected static $lists = NULL;

  public static function byContact($contact) {
    $subscriptions = array();
    foreach ($contact->allEmail() as $address) {
      $subscriptions[$address['value']] = array();
    }

    $sql = 'SELECT * FROM {campaignion_newsletters_subscriptions} WHERE email IN(:emails)';
    foreach (db_query($sql, array(':emails' => array_keys($subscriptions))) as $row) {
      $subscriptions[$row->email][$row->list_id] = $row->list_id;
    }
    return new static($subscriptions);
  }

  public function __construct($subscriptions) {
    $this->subscriptions = $subscriptions;
  }

  public static function lists() {
    if (!isset(static::$lists)) {
      static::$lists = NewsletterList::listAll();
    }
    return static::$lists;
  }

  public function update($subscriptions) {
    $this->subscriptions = $subscriptions;
  }

  public function unsubscribeAll() {
    foreach ($this->subscriptions as $email => $lists) {
      $this->subscriptions[$email] = array();
    }
  }

  public function save() {
    $lists = static::lists();
    foreach ($lists as $list_id => $list) {
      foreach ($this->subscriptions as $email => $subscriptions) {
        if (!empty($subscriptions[$list_id])) {
          $list->subscribe($email);
        } else {
          $list->unsubscribe($email);
        }
      }
    }
  }

  public function optionsArray() {
    $options = array();
    foreach (static::lists() as $list_id => $list) {
      $options[$list_id] = $list->title;
    }
    return $options;
  }

  public function values($email) {
    $values = array();
    foreach ($this->subscriptions[$email] as $list_id => $subscribed) {
      $values[$list_id] = $subscribed ? $list_id : 0;
    }
    return $values;
  }
}

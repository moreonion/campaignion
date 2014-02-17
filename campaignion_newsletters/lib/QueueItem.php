<?php

namespace Drupal\campaignion_newsletters;

class QueueItem extends \Drupal\little_helpers\DB\Model {
  const SUBSCRIBE = 1;
  const UNSUBSCRIBE = 0;

  public $list_id;
  public $email;
  public $created;
  public $action;
  public $data;

  protected static $table = 'campaignion_newsletters_queue';
  protected static $key = array('list_id', 'email');
  protected static $values = array('created', 'action', 'data');
  protected static $serialize = array('data' => TRUE);
  protected static $serial = FALSE;

  public static function load($list_id, $email) {
    $table = static::$table;
    $keys = array(':list_id' => $list_id, ':email' => $email);
    $result = db_query("SELECT * FROM {{$table}} WHERE list_id=:list_id AND email=:email", $keys);
    if ($row = $result->fetch()) {
      return new static($row, FALSE);
    }
  }

  public static function byData($data) {
    if ($item = static::load($data['list_id'], $data['email'])) {
      $item->__construct($data, FALSE);
    }
    else {
      $item = new static($data);
    }
    return $item;
  }

  public static function oldest($limit) {
    $table = static::$table;
    $result = db_select(static::$table, 'i')
      ->fields('i')
      ->orderBy('created')
      ->range(0, $limit)
      ->execute();
    $items = array();
    foreach ($result as $row) {
      $items[] = new static($row, FALSE);
    }
    return $items;
  }

  public function __construct($data = array(), $new = TRUE) {
    parent::__construct($data, $new);
    if (!isset($this->created)) {
      $this->created = time();
    }
  }
}

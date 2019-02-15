<?php

namespace Drupal\campaignion_newsletters;

use Drupal\little_helpers\DB\Model;

/**
 * DB-Model for {campaignion_newsletters_lists}.
 */
class NewsletterList extends Model {

  public $list_id;
  public $source;
  public $identifier;
  public $language;
  public $title;
  public $data;
  public $updated;

  protected static $table = 'campaignion_newsletters_lists';
  protected static $key = array('list_id');
  protected static $values = array('source', 'identifier', 'title', 'language', 'data', 'updated');
  protected static $serial = TRUE;
  protected static $serialize = array('data' => TRUE);

  /**
   * Load all lists from one source.
   */
  public static function bySource($source) {
    return static::loadQuery(['source' => $source]);
  }

  /**
   * Generic function to load using conditions and order criteria.
   */
  protected static function loadQuery($conditions = [], $order_by = []) {
    $q = db_select(static::$table, 'l')->fields('l');
    foreach ($conditions as $field => $value) {
      if (is_numeric($field)) {
        list($field, $value, $op) = $value;
      }
      else {
        $op = NULL;
      }
      $q->condition($field, $value, $op);
    }
    foreach ($order_by as $field => $direction) {
      $q->orderBy($field, $direction);
    }
    $lists = [];
    foreach ($q->execute() as $row) {
      $lists[$row->list_id] = new static($row);
    }
    return $lists;
  }

  public static function listAll() {
    return static::loadQuery([], ['title' => 'ASC']);
  }

  public static function load($id) {
    if ($rows = static::loadQuery(['list_id' => $id])) {
      return $rows[$id];
    }
  }

  public static function notUpdatedSince($time) {
    return static::loadQuery([['updated', $time, '<']]);
  }

  public static function byIdentifier($source, $identifier) {
    $rows = static::loadQuery([
      'source' => $source,
      'identifier' => $identifier,
    ]);
    return reset($rows);
  }

  public static function fromData($data) {
    $adata = array();
    foreach ($data as $k => $v) {
      $adata[$k] = $v;
    }
    if ($item = self::byIdentifier($data['source'], $data['identifier'])) {
      unset($adata['list_id']);
      $item->__construct($adata);
      return $item;
    } else {
      return new static($data, TRUE);
    }
  }

  public function __construct($data = array(), $new = FALSE) {
    parent::__construct($data, $new);
    foreach ($data as $k => $v) {
      $this->$k = (is_string($v) && !empty(self::$serialize[$k])) ? unserialize($v) : $v;
    }
    if (!isset($this->language)) {
      $this->language = language_default('language');
    }
  }

  public function provider() {
    return ProviderFactory::getInstance()->providerByKey($this->source);
  }

  /**
   * Subscribe a single email-address to this newsletter.
   */
  public function subscribe($email, $fromProvider = FALSE) {
    $fields = array(
      'list_id' => $this->list_id,
      'email' => $email,
    );
    // MySQL supports multi-value merge queries, drupal does not so far,
    // so we could replace the following by a direct call to db_query().
    db_merge('campaignion_newsletters_subscriptions')
      ->key($fields)
      ->fields($fields)
      ->execute();

    if (!$fromProvider) {
      QueueItem::byData(array(
        'list_id' => $this->list_id,
        'email' => $email,
        'action' => QueueItem::SUBSCRIBE,
      ))->save();
    }
  }

  public function unsubscribe($email, $fromProvider = FALSE) {
    db_delete('campaignion_newsletters_subscriptions')
      ->condition('list_id', $this->list_id)
      ->condition('email', $email)
      ->execute();

    if (!$fromProvider) {
      QueueItem::byData(array(
        'list_id' => $this->list_id,
        'email' => $email,
        'action' => QueueItem::UNSUBSCRIBE,
      ))->save();
    }
  }
}

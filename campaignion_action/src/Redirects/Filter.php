<?php

namespace Drupal\campaignion_action\Redirects;

use Drupal\little_helpers\DB\Model;

/**
 * Model class for redirect filters.
 */
class Filter extends Model {
  protected static $table  = 'campaignion_action_redirect_filter';
  protected static $key = ['id'];
  protected static $values = ['redirect_id', 'weight', 'type', 'config'];
  protected static $serialize = ['config' => TRUE];

  public $id;
  public $redirect_id;
  public $weight = 0;
  public $type;
  public $config = [];

  public static function fromArray($data) {
    $config = $data + ['id' => NULL, 'weight' => 0];
    unset($config['redirect_id']);
    $data = [];
    foreach (['id', 'weight', 'type'] as $k) {
      $data[$k] = $config[$k];
      unset($config[$k]);
    }
    $data['config'] = $config;
    return new static($data);
  }

  public function __construct($data = array(), $new = TRUE) {
    parent::__construct($data, $new);
  }

  /**
   * Update filter data from array.
   */
  public function setData($data) {
    unset($data['id']);
    unset($data['redirect_id']);
    $this->__construct($data);
  }

  /**
   * Get filters for given redirect_ids.
   *
   * @param array $ids
   *   Redirect IDs to get the filters for.
   * @return array
   *   Filters ordered by redirect_id and weight, and keyed by their Id.
   */
  public static function byRedirectIds($ids) {
    // DB queries doesn't work well with empty arrays in IN() clauses.
    if (!$ids) {
      return [];
    }
    $result = db_select(static::$table, 'f')
      ->fields('f')
      ->condition('redirect_id', $ids)
      ->orderBy('redirect_id')
      ->orderBy('weight')
      ->execute();
    $filters = [];
    foreach ($result as $row) {
      $filters[$row->id] = new static($row, FALSE);
    }
    return $filters;
  }

  public function toArray() {
    $data = [];
    foreach (array_merge(static::$key, static::$values) as $k) {
      $data[$k] = $this->$k;
    }
    if (isset($data['config']) && is_array($data['config'])) {
      $config = $data['config'];
      unset($data['config']);
      $data += $config;
    }
    unset($data['weight']);
    unset($data['redirect_id']);
    return $data;
  }

  public function match($submission) {
    if ($this->type == 'target-attribute') {
      $data['contact'] = $target;
      $data['constituency'] = $constituency;
      $name = $this->config['attributeName'];
      $key_exists = NULL;
      $value = drupal_array_get_nested_value($data, explode('.', $name), $key_exists);
      return $key_exists ? $this->matchValue($value) : FALSE;
    }
    return TRUE;
  }

  protected function matchValue($target_value) {
    $value = $this->config['value'];
    switch ($this->config['operator']) {
      case '==':
        return $target_value == $value;
      case '!=':
        return $target_value != $value;
      case 'regexp':
        return (bool) preg_match("/$value/", $target_value);
    }
    return FALSE;
  }

  /**
   * Clear out all IDs in order to create a real copy.
   */
  public function __clone() {
    $this->id = NULL;
    $this->new = TRUE;
  }

}

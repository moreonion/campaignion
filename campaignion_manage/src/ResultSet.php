<?php

namespace Drupal\campaignion_manage;

class ResultSet extends \Drupal\little_helpers\DB\Model {
  protected static $table = 'campaignion_manage_result_meta';
  protected static $key = array('id');
  protected static $values = array('uid', 'step', 'created');
  protected static $serial = TRUE;
  public $id = NULL;
  public $uid;
  public $step = NULL;
  public $created = NULL;

  public function __construct(array $data = array(), $new = TRUE) {
    $data += array(
      'uid' => $GLOBALS['user']->uid,
      'created' => REQUEST_TIME,
    );
    parent::__construct($data, $new);
  }

  public static function load($uid = NULL, $step = NULL) {
    $uid = $uid ? $uid : $GLOBALS['user']->uid;
    $t = static::$table;
    db_query("SELECT * FROM {{$t}} WHERE uid=:uid AND step=:step", array(':uid' => $uid, ':step' => $step));
  }

  public function save() {
    if ($this->isNew()) {
      if ($old = static::load($this->uid, $this->step)) {
        $old->delete();
      }
    }
    parent::save();
  }

  public function delete() {
    if ($this->isNew()) {
      return;
    }
    $this->purge();
    parent::delete();
  }

  public function purge() {
    $filter = array(':id' => $this->id);
    db_query('DELETE FROM {campaignion_manage_result} WHERE meta_id=:id', $filter);
  }

  public function count() {
    $filter = array(':id' => $this->id);
    $result = db_query('SELECT count(*) FROM {campaignion_manage_result} WHERE meta_id=:id', $filter);
    return $result->fetchField();
  }

  public function nextIds($start, $limit) {
    $filter = array(':id' => $this->id, ':start' => $start);
    $result = db_query_range('SELECT contact_id FROM {campaignion_manage_result} WHERE meta_id=:id AND contact_id>:start', 0, $limit, $filter);
    return $result->fetchCol();
  }
}

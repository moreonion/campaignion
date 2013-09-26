<?php

namespace Drupal\campaignion;

class Activity implements Interfaces\Activity {
  public function __construct($data = array()) {
    foreach ($data as $k => &$v) {
      $this->{$k} = &$v;
    }
  }
  
  public static function load($activity_id) {
    return db_select('campaignion_activity', 'a')
      ->fields('a')
      ->condition('activity_id', $activity_id)
      ->execute()
      ->fetchObject(get_called_class());
  }
  
  public function save() {
    $data = (array) $this;
    unset($data['activity_id']);
    if (isset($this->activity_id)) {
      db_update('campaignion_activity')
        ->condition('activity_id', $this->activity_id)
        ->fields($data)
        ->execute();
    } else {
      $this->activity_id = db_insert('campaignion_activity')
        ->fields($data)
        ->execute();
    }
  }
  
  public function delete() {
    if (isset($this->activity_id)) {
      db_delete('campaignion_activity')->condition('activity_id', $this->activity_id)->execute();
    }
  }
}

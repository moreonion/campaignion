<?php

namespace Drupal\campaignion\Wizard;

class Status extends \Drupal\little_helpers\DB\Model {
  protected static $table = 'campaignion_wizard_status';
  protected static $key = array('nid');
  protected static $values = array('step');
  protected static $serial = FALSE;

  public $nid;
  public $step;

  public static function loadOrCreate($nid) {
    $table = self::$table;
    $item = db_query("SELECT * FROM {{$table}} WHERE nid=:nid", array(':nid' => $nid))
      ->fetchObject(get_called_class());
    if (!$item) {
      $item = new static(array('nid' => $nid), TRUE);
    }
    return $item;
  }
}

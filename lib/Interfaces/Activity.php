<?php

namespace Drupal\campaignion\Interfaces;

interface Activity {
  public static function load($id);
  public function save();
  public function delete();
}

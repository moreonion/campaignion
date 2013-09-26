<?php

namespace Drupal\campaignion\Interfaces;

interface ActivityType {
  public function alterQuery(\SelectQuery $query, $operator);
  public function createActivityFromRow($data);
}

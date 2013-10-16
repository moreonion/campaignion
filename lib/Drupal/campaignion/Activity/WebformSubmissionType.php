<?php

namespace Drupal\campaignion\Activity;

class WebformSubmissionType implements \Drupal\campaignion\Interfaces\ActivityType {
  public function alterQuery(\SelectQuery $query, $operator) {
  }
  public function createActivityFromRow($data) {
    return new WebformSubmission($data);
  }
}

<?php

namespace Drupal\campaignion\Donation;

use Drupal\campaignion\ActionType as BaseActionType;

class ActionType extends BaseActionType {
  public function defaultTemplateNid() {
    $query = new \EntityFieldQuery();

    $result = $query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'webform_template_type')
    ->propertyCondition('title', 'Global 2000 Donation Webform Template')
    ->execute();

    $nids = isset($result['node']) ? array_keys($result['node']) : array(NULL);
    return $nids[0];
  }
}

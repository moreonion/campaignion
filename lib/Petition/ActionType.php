<?php

namespace Drupal\campaignion\Petition;

use Drupal\campaignion\ActionType as BaseActionType;

class ActionType extends BaseActionType {
  public function defaultTemplateNid() {
    $query = new \EntityFieldQuery();

    $result = $query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'webform_template_type')
    ->propertyCondition('title', 'Minimal supporter form')
    ->execute();

    $nids = isset($result['node']) ? array_keys($result['node']) : array(NULL);
    return $nids[0];
  }
}
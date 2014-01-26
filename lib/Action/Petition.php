<?php

namespace Drupal\campaignion\Action;

use \Drupal\campaignion\Wizard\PetitionWizard;

class Petition extends TypeBase {
  public function defaultTemplateNid() {
    $query = new \EntityFieldQuery();

    $result = $query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'webform_template_type')
    ->propertyCondition('title', 'Minimal supporter form')
    ->execute();

    $nids = isset($result['node']) ? array_keys($result['node']) : array(NULL);
    return $nids[0];
  }

  public function wizard($node = NULL) {
    return new PetitionWizard($this->parameters, $node, $this->type);
  }
}

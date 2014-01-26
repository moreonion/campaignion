<?php

namespace Drupal\campaignion\Action;

use \Drupal\campaignion\Wizard\DonationWizard;

class Donation extends TypeBase {
  public function defaultTemplateNid() {
    $query = new \EntityFieldQuery();

    $result = $query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'webform_template_type')
    ->propertyCondition('title', 'Global 2000 Donation Webform Template')
    ->execute();

    $nids = isset($result['node']) ? array_keys($result['node']) : array(NULL);
    return $nids[0];
  }

  public function wizard($node = NULL) {
    return new DonationWizard($this->parameters, $node, $this->type);
  }
}

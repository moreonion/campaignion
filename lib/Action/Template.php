<?php

namespace Drupal\campaignion\Action;

use \Drupal\campaignion\Wizard\WebformTemplateWizard;

class Template extends TypeBase {
  public function wizard($node = NULL) {
    return new WebformTemplateWizard($this->parameters, $node, $this->type);
  }
}

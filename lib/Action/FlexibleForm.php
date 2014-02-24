<?php

namespace Drupal\campaignion\Action;

use \Drupal\campaignion\Wizard\WebformWizard;

class FlexibleForm extends TypeBase {

  public function wizard($node = NULL) {
    return new WebformWizard($this->parameters, $node, $this->type);
  }
}

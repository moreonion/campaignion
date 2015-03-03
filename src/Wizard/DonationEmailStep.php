<?php

namespace Drupal\campaignion\Wizard;

class DonationEmailStep extends EmailStep {

  public function __construct($wizard) {
    parent::__construct($wizard);
    unset($this->emailInfo['confirmation']);
  }

}

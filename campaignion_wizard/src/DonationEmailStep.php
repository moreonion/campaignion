<?php

namespace Drupal\campaignion_wizard;

class DonationEmailStep extends EmailStep {

  public function __construct($wizard) {
    parent::__construct($wizard);
    unset($this->emailInfo['confirmation']);
  }

}

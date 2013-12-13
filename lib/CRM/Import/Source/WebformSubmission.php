<?php

namespace Drupal\campaignion\CRM\Import\Source;

class WebformSubmission extends \Drupal\little_helpers\WebformSubmission implements SourceInterface {

  public function value($key) {
    return $this->valueByKey($key);
  }
}

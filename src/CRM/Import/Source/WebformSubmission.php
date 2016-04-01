<?php

namespace Drupal\campaignion\CRM\Import\Source;

use Drupal\little_helpers\Webform\Submission;

class WebformSubmission extends Submission implements SourceInterface {

  public function value($key) {
    return $this->valueByKey($key);
  }
}

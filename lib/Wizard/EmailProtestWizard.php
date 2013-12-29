<?php

namespace Drupal\campaignion\Wizard;

class EmailProtestWizard extends NodeWizard {
  public $steps = array(
    'content' => 'EmailProtestContentStep',
    'target'  => 'EmailProtestTargetStep',
    'form'    => 'WebformStep',
    'emails'  => 'EmailProtestEmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'ConfirmStep',
  );
}

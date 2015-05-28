<?php

namespace Drupal\campaignion_wizard;

class EmailProtestWizard extends NodeWizard {
  public $steps = array(
    'content' => 'ContentStep',
    'target'  => 'EmailProtestTargetStep',
    'form'    => 'WebformStep',
    'emails'  => 'EmailProtestEmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'ConfirmStep',
  );
}

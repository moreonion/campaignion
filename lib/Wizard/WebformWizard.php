<?php

namespace Drupal\campaignion\Wizard;

class WebformWizard extends NodeWizard {
  public $steps = array(
    'content' => 'WebformContentStep',
    'form'    => 'WebformStep',
    'emails'  => 'EmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'ConfirmStep',
  );
}

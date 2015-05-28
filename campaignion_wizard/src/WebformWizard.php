<?php

namespace Drupal\campaignion_wizard;

class WebformWizard extends NodeWizard {
  public $steps = array(
    'content' => 'ContentStep',
    'form'    => 'WebformStep',
    'emails'  => 'EmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'ConfirmStep',
  );
}

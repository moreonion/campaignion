<?php

namespace Drupal\campaignion_wizard;

class PetitionWizard extends NodeWizard {
  public $steps = array(
    'content' => 'ContentStep',
    'form'    => 'PetitionWebformStep',
    'emails'  => 'EmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'ConfirmStep',
  );
}

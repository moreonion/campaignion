<?php

namespace Drupal\campaignion\Wizard;

class PetitionWizard extends NodeWizard {
  public $steps = array(
    'content' => 'ContentStep',
    'form'    => 'PetitionWebformStep',
    'emails'  => 'EmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'PetitionConfirmStep',
  );
}


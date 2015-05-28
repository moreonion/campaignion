<?php

namespace Drupal\campaignion_wizard;

class DonationWizard extends NodeWizard {
  public $steps = array(
    'content' => 'ContentStep',
    'form'    => 'WebformStep',
    'emails'  => 'DonationEmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'ConfirmStep',
  );
}

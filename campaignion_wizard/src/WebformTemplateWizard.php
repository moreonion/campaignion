<?php

namespace Drupal\campaignion_wizard;

class WebformTemplateWizard extends NodeWizard {
  public $steps = array(
    'content' => 'ContentStep',
    'form'    => 'WebformStep',
    'emails'  => 'EmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'WebformTemplateConfirmStep',
  );
}

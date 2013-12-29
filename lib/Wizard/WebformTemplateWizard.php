<?php

namespace Drupal\campaignion\Wizard;

class WebformTemplateWizard extends NodeWizard {
  public $steps = array(
    'content' => 'WebformTemplateContentStep',
    'form'    => 'WebformStep',
    'emails'  => 'EmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'WebformTemplateConfirmStep',
  );
}

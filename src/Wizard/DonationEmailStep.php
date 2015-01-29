<?php

namespace Drupal\campaignion\Wizard;

class DonationEmailStep extends EmailStep {

  public function stepForm($form, &$form_state) {

    $form = WizardStep::stepForm($form, $form_state);
    $node = $this->wizard->node;

    $form['#tree'] = TRUE;
    $form['wizard_head']['#tree'] = FALSE;

    $this->emails['thank_you'] = $email = new Email($node, 'thank_you', self::WIZARD_THANK_YOU_EID);
    $messages = array(
      'toggle_title' => t('Enable a thank you email'),
      'email_title'  => t('Thank you email'),
    );
    $form += $email->form($messages, $form_state);

    return $form;
  }

  public function submitStep($form, &$form_state) {

    $node   = $this->wizard->node;
    $values =& $form_state['values'];

    $this->emails['thank_you']->submit($form, $form_state, 0 /*type always send*/);
  }

}

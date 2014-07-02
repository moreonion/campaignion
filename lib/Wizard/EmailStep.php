<?php

namespace Drupal\campaignion\Wizard;

class EmailStep extends WizardStep {

  const WIZARD_THANK_YOU_EID = 1;
  const WIZARD_CONFIRMATION_REQUEST_EID = 2;
  const WIZARD_NOTIFICATION_EID = 3;

  protected $step  = 'emails';
  protected $title = 'Emails';
  protected $emails = array();

  protected function loadIncludes() {
    module_load_include('inc', 'webform', 'includes/webform.emails');
    module_load_include('inc', 'webform', 'includes/webform.components');
  }

  public function stepForm($form, &$form_state) {

    $form = parent::stepForm($form, $form_state);
    $node = $this->wizard->node;

    $form['#tree'] = TRUE;
    $form['wizard_head']['#tree'] = FALSE;

    $this->emails['confirmation'] = $email = new Email($node, 'confirmation_request', self::WIZARD_CONFIRMATION_REQUEST_EID);
    /* double optin / confirmation request email */
    $messages = array(
      'toggle_title' => t('Enable email confirmation (double opt in)'),
      'email_title'  => t('Email confirmation'),
    );
    $form += $email->form($messages, $form_state);

    $form['or'] = array(
      '#type'   => 'markup',
      '#markup' => '<div class="thank-you-outer-or"><span class="thank-you-line-or">&nbsp;</span></div>',
    );

    $this->emails['thank_you'] = $email = new Email($node, 'confirmation_or_thank_you', self::WIZARD_THANK_YOU_EID);
    $messages = array(
      'toggle_title' => t('Enable a thank you email'),
      'email_title'  => t('Thank you email'),
    );
    $form += $email->form($messages, $form_state);

    $form['or2'] = array(
      '#type'   => 'markup',
      '#markup' => '<div class="thank-you-outer-or"><span class="thank-you-line-or">&nbsp;</span></div>',
    );

    $this->emails['notification'] = $email = new Email($node, 'notification', self::WIZARD_NOTIFICATION_EID);
    $messages = array(
      'toggle_title' => t('Enable a notification email'),
      'email_title'  => t('Notification email'),
    );
    $form += $email->form($messages, $form_state);

    // we are using only custom and default options
    // therefore if the custom address is set to campaignion, we take this as the
    // default for the notification option
    if ($form['notification_email']['from_address_custom']['#default_value'] === 'you@example.com') {
      $form['notification_email']['from_address_custom']['#default_value'] = '';
    }
    $form['notification_email']['from_address_option']['#default_value'] = 'You';

    $form['notification_email']['email_option']['#access'] = TRUE;
    $form['notification_email']['email_option']['#default_value'] = 'custom';
    unset($form['notification_email']['email_option']['#options']['component']);
    unset($form['notification_email']['email_option']['#options']['default']);
    $form['notification_email']['email_custom']['#access'] = TRUE;
    if (!$form['notification_email']['email_custom']['#default_value']) {
      $form['notification_email']['email_custom']['#default_value'] = 'noreply@example.com';
    }
    // add the new option in beginning
    $form['notification_email']['from_address_option']['#options'] =
      array('campaignion' => 'Default: <em class="placeholder">you@example.com</em>')
      + $form['notification_email']['from_address_option']['#options'];
    unset($form['notification_email']['from_address_option']['#options']['default']);

    return $form;
  }

  public function checkDependencies() {
    return isset($this->wizard->node->nid);
  }

  public function validateStep($form, &$form_state) {
    foreach ($this->emails as $email) {
      $email->validate($form, $form_state);
    }
  }

  public function submitStep($form, &$form_state) {
    $node = $this->wizard->node;
    $values =& $form_state['values'];

    $this->emails['confirmation']->submit($form, $form_state, 1);

    // if we want an confirmation the thank you has to be a conditional email.
    // otherwise it's sent immediately.
    $type = $values['confirmation_request_email']['confirmation_request_check'] == 1 ? 2 : 0;
    $this->emails['thank_you']->submit($form, $form_state, $type);

    $values['notification_email']['from_address_campaignion'] = 'you@example.com'; // Default notification from address
    $this->emails['notification']->submit($form, $form_state, 0);
  }

  public function status() {
    return array(
      'caption' => t('Thank you email'),
      'message' => t("You've set up a \"thank you\" email that will be sent to your supporters."),
    );
  }
}

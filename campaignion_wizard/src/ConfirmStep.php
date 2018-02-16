<?php

namespace Drupal\campaignion_wizard;

/**
 * Wizard confirmation form step.
 *
 * Displays a summary of all previous steps and renders submit buttons.
 */
class ConfirmStep extends WizardStep {
  protected $step = 'confirm';
  protected $title = 'Confirm';

  /**
   * Render the status messages for all steps.
   *
   * @return array
   *   Array of form-API arrays containing the status messages.
   */
  protected function generateStatusMessages() {
    $button = array(
      '#type' => 'submit',
      '#wizard type' => 'next',
      '#value' => t('Edit'),
      '#submit' => array('ctools_wizard_submit'),
      '#executes_submit_callback' => TRUE,
      '#attributes' => array('class' => array('confirm-edit-button')),
      '#limit_validation_errors' => array(),
    );
    $container = [
      '#type' => 'container',
      '#attributes' => ['class' => ['confirm-edit-wrapper']],
    ];
    $messages = [];
    foreach ($this->wizard->stepHandlers as $urlpart => $step) {
      // Allow steps to don't produce a status message.
      if (!($status = $step->status())) {
        continue;
      }
      $messages['to_' . $urlpart] = [
        'button' => ['#next' => $urlpart, '#name' => 'to_' . $urlpart] + $button,
        'caption' => ['#markup' => "<h2>{$status['caption']}</h2>"],
        'description' => ['#markup' => "<p>{$status['message']}</p>"],
      ] + $container;
    }
    return $messages;
  }

  /**
   * Generate the submit buttons to show below the status messages.
   *
   * @return array
   *   Form-API array containing the submit buttons.
   */
  protected function generateButtons($form) {
    unset($form['buttons']['previous']);
    $buttons = [
      '#weight' => 1000,
      '#attributes' => ['class' => ['form-submit']],
    ] + $form['buttons'];

    $buttons['return'] = [
      '#value' => t('Publish now!'),
      '#type' => 'submit',
      '#name' => 'finish',
      '#wizard type' => 'return',
      '#attributes' => ['class' => ['button-finish']],
    ];
    $buttons['schedule'] = [
      '#type' => 'submit',
      '#value' => t('Schedule publishing'),
      '#name' => 'schedule',
      '#weight' => 1010,
      '#access' => FALSE,
      '#attributes' => ['class' => ['button-finish-other']],
    ];
    $buttons['draft'] = [
      '#type' => 'submit',
      '#value' => t('Save as draft'),
      '#name' => 'draft',
      '#weight' => 1020,
      '#wizard type' => 'return',
      '#attributes' => ['class' => ['button-finish-other']],
    ];
    return $buttons;
  }

  /**
   * {@inheritdoc}
   */
  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);

    $form['confirm_container'] = [
      '#type' => 'container',
    ] + $this->generateStatusMessages();

    $form['confirm_container']['buttons'] = $this->generateButtons($form);
    unset($form['buttons']);

    return $form;
  }

  /**
   * Form submission handler for this wizard step.
   */
  public function submitStep($form, &$form_state) {
    if (isset($this->wizard->node->nid)) {
      if (isset($form_state['clicked_button']['#name'])) {
        $node = $this->wizard->node;
        $type_name = node_type_get_name($node);
        switch ($form_state['clicked_button']['#name']) {
          case 'finish':
            $node->status = 1;
            node_save($node);
            drupal_set_message(t('!type published successfully.', array('!type' => $type_name)), 'status');
            $form_state['redirect'] = 'node/' . $node->nid;
            break;

          case 'draft':
            $node->status = 0;
            node_save($node);
            drupal_set_message(t('!type saved as draft.', array('!type' => $type_name)), 'status');
            $form_state['redirect'] = 'node/' . $node->nid;
            break;

          case 'schedule':
            drupal_set_message(t('Schedule is not implemented yet.'), 'warning');
            break;
        }
      }
    }
    else {
      drupal_set_message(t('Where is my node? Did you fill out the first step?'), 'error');
      // Stay on the page, do not redirect.
      $form_state['redirect'] = '';
    }
  }

}

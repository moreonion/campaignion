<?php

namespace Drupal\campaignion_wizard;

abstract class WizardStep extends \Drupal\oowizard\WizardStep {
  public function stepForm($form, &$form_state) {
    $form['#theme'] = 'campaignion_wizard_form';
    $form['trail'] = $this->wizard->trail();
    $form['wizard_advanced'] = array(
      '#type' => 'container',
      '#weight' => 2000,
    );

    $form['#attributes']['class'][] = 'wizard-form';
    $form['#attributes']['class'][] = 'wizard-main-container';

    $form['buttons']['#tree'] = FALSE;
    $form['buttons']['#weight'] = -20;
    $form['buttons']['next']['#value'] = t('Next');

    if (isset($form['buttons']['return'])) {
      $label = (isset($this->wizard->node->status) && $this->wizard->node->status) ? t('Save & return') : t('Save as draft');
      $form['buttons']['return']['#value'] = $label;
    }
    return $form;
  }

  public function status() {
    return NULL;
  }
}

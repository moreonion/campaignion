<?php

namespace Drupal\campaignion\Wizard;

abstract class WizardStep extends \Drupal\oowizard\WizardStep {
  public function stepForm($form, &$form_state) {
    $form['wizard_head'] = array(
      '#type' => 'container',
      '#weight' => -3000,
      '#suffix' => '<div id="wizard-main">'
    );
    $form['wizard_head']['#attributes'] = array('class' => array('clearfix'));

    // wizard_main_close is needed to close the #wizard-main div
    // (opened by wizard_head)
    $form['wizard_main_close'] = array(
      '#type' => 'container',
      '#prefix' => '</div>',
      '#weight' => 3000,
      '#attributes' => array('class' => array('element-invisible'), 'id' => ''),
    );

    $form['wizard_advanced'] = array(
      '#type' => 'container',
      '#weight' => 2000,
    );

    $form['#attributes']['class'][] = 'wizard-form';
    $form['#attributes']['class'][] = 'wizard-main-container';

    $form['buttons']['#weight'] = -20;
    $form['buttons']['next']['#value'] = t('Next');

    if (isset($form['buttons']['return'])) {
      $form['buttons']['return']['#value'] = t('Save & return');
    }

    $form['wizard_head']['buttons'] = $form['buttons'];
    unset($form['buttons']);

    return $form;
  }

  public function status() {
    return NULL;
  }
}
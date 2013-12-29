<?php

namespace Drupal\campaignion\Wizard;

class WebformTemplateContentStep extends ContentStep {
  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);

    $form['body']['#access'] = FALSE;
    $form['wizard_advanced']['#access'] = FALSE;
    $form['toggle_wizard_advanced']['#access'] = FALSE;

    return $form;
  }
}

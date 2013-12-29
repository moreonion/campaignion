<?php

namespace Drupal\campaignion\Wizard;

class WebformaContentStep extends ContentStep {
  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);

    $form['field_thank_you_pages'] = FALSE;

    // wizard-only default values
    if (!isset($this->wizard->node->nid) && !isset($this->wizard->node->title)) {
      $form['title']['#default_value'] = t('Flexible form');
    }

    $form['field_main_image']['#weight'] = -10;
    $form['title']['#weight'] = 210;
    $form['body']['#weight'] = 220;
    $form['field_webform_pgbar']['#weight'] = 230;

    return $form;
  }
}

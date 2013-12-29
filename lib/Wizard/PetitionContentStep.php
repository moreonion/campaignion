<?php

namespace Drupal\campaignion\Wizard;

class PetitionContentStep extends ContentStep {
  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);

    // wizard-only default values
    if (!isset($this->wizard->node->nid) && !isset($this->wizard->node->title)) {
      $form['title']['#default_value'] = t('Petition');
    }

    return $form;
  }

  public function status() {
    return array(
      'caption' => t('Your copy is great'),
      'message' => t('You have added content, a nice picture and a convincing title.'),
    );
  }
}

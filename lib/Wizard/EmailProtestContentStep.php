<?php

namespace Drupal\campaignion\Wizard;

class EmailProtestContentStep extends ContentStep {

  public function stepForm($form, &$form_state) {

    $form = parent::stepForm($form, $form_state);

    if (!isset($this->wizard->node->nid) && !isset($this->wizard->node->title)) {
      $form['title']['#default_value'] = t('Email Protest');
    }

    $form['field_thank_you_pages'] = FALSE;

    $form['field_main_image']['#weight'] = -10;
    $form['title']['#weight'] = 210;
    $form['body']['#weight'] = 220;
    $form['field_email_protest_pgbar']['#weight'] = 230;

    $form['field_protest_target_options']['#access'] = FALSE;
    $form['field_protest_target']['#access'] = FALSE;

    return $form;
  }

  public function status() {
    return array(
      'caption' => t('Your copy is great'),
      'message' => t('You have added content, a nice picture and a convincing title.'),
    );
  }
}


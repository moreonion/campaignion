<?php

namespace Drupal\campaignion\Wizard;

class DonationContentStep extends ContentStep {
  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);

    $form['field_thank_you_pages'] = FALSE;

    // wizard-only default values
    if (!isset($this->wizard->node->nid) && !isset($this->wizard->node->title)) {
      $form['title']['#default_value'] = t('Donation');
    }

    $form['field_main_image']['#weight'] = -10;
    $form['title']['#weight'] = 210;
    $form['body']['#weight'] = 220;
    $form['field_donation_pgbar']['#weight'] = 230;

    $source = &$form['field_donation_pgbar']['und'][0]['options']['source'];
    $source['form_key']['#default_value'] = 'donation_amount';
    $source['#access'] = FALSE;

    return $form;
  }

  public function status() {
    return array(
      'caption' =>     t('Your copy is great'),
      'message' => t('You have added content, a nice picture and a convincing title.'),
    );
  }
}

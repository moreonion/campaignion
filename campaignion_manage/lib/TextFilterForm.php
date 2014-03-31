<?php

/**
 * &file
 */

namespace Drupal\campaignion_manage;

class TextFilterForm {
  public function __construct() {
  }

  public function form(array $form, array &$form_state) {
    $form['#tree'] = TRUE;

    $form['text_filter'] = array(
      '#type'       => 'textfield',
      '#title'      => t(''),
      '#maxlength'  => 255,
      '#size'       => 40,
      '#attributes' => array('class' => array('campaignion_manage_text_filter')),
    );

    return $form;
  }
}
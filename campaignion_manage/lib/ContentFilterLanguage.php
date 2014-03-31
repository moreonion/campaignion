<?php

namespace Drupal\campaignion_manage;

class ContentFilterLanguage implements FilterInterface {
  public function formElement(array &$form, array &$form_state, array &$values) {
    $options = array();
    foreach (language_list() as $code => $language) {
      $options[$code] = $language->native;
    }
    $form['language'] = array(
      '#type' => 'select',
      '#title' => t('Language'),
      '#options' => $options,
      '#default_value' => isset($values) ? $values : NULL,
    );
  }
  public function machineName() { return 'language'; }
  public function title() { return t('Language'); }
  public function apply($query, array $values) {
    $query->getQuery()->condition('n.language', $values['language']);
  }
  public function nrOfInstances() {
    return 1;
  }
}

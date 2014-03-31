<?php

namespace Drupal\campaignion_manage;

class ContentFilterStatus implements FilterInterface {
  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['status'] = array(
      '#type' => 'select',
      '#title' => t('Status'),
      '#options' => array(1 => t('Published'), 0 => t('Unpublished')),
      '#default_value' => isset($values) ? $values : NULL,
    );
  }
  public function machineName() { return 'status'; }
  public function title() { return t('Status'); }
  public function apply($query, array $values) {
    $query->getQuery()->condition('n.status', $values['status']);
  }
  public function nrOfInstances() {
    return 1;
  }
}

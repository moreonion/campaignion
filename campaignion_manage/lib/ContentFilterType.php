<?php

namespace Drupal\campaignion_manage;

class ContentFilterType implements FilterInterface {
  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['type'] = array(
      '#type' => 'select',
      '#title' => t('Type'),
      '#options' => node_type_get_names(),
      '#default_value' => isset($values) ? $values : NULL,
    );
  }
  public function machineName() { return 'type'; }
  public function title() { return t('Content type'); }
  public function apply($query, array $values) {
    $query->getQuery()->condition('n.type', $values['type']);
  }
  public function nrOfInstances() {
    return 1;
  }
}

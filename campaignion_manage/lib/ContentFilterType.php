<?php

namespace Drupal\campaignion_manage;

class ContentFilterType {
  public function form(&$form, &$form_state, &$values) {
    $form['type'] = array(
      '#type' => 'select',
      '#title' => t('Type'),
      '#options' => node_type_get_names(),
      '#default_value' => isset($values['type']) ? $values['type'] : NULL,
    );
  }
  public function machineName() { return 'type'; }
  public function title() { return t('Content type'); }
  public function apply($query, $values) {
    $query->getQuery()->condition('n.type', $values['type']);
  }
}

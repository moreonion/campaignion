<?php

namespace Drupal\campaignion_manage\Filter;

class ContentStatus implements FilterInterface {
  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['status'] = array(
      '#type' => 'select',
      '#title' => t('Publishing state'),
      '#options' => array(1 => t('Published'), 0 => t('Unpublished')),
      '#default_value' => isset($values) ? $values : NULL,
    );
    $form['#attributes']['class'][] = 'campaignion-manage-filter-status';
  }
  public function title() { return t('Publishing state'); }
  public function apply($query, array $values) {
    $query->getQuery()->condition('n.status', $values['status']);
  }
  public function nrOfInstances() { return 1; }

  public function isApplicable() { return TRUE; }
}

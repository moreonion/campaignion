<?php

namespace Drupal\campaignion_manage\Filter;

class ContentTitle implements FilterInterface {

  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['title'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Filter by typing the title'),
      '#maxlength'     => 255,
      '#size'          => 40,
      '#default_value' => isset($values['title']) ? $values['title'] : NULL,
    );
    $form['#attributes']['class'][] = 'campaignion-manage-filter-title';
  }

  public function title() { return t('Filter by typing the title'); }

  public function apply($query, array $values) {
    if (!empty($values['title'])) {
      $search = preg_replace('/[[:blank:]]*/', '%', $values['title']);
      $query->getQuery()->where('LOWER(n.title) LIKE :search_string', array( ':search_string' => '%' . strtolower($search) . '%'));
    }
  }

  public function nrOfInstances() { return 1; }

  public function isApplicable() { return TRUE; }
}

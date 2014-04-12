<?php

namespace Drupal\campaignion_manage\Filter;

class SupporterName extends Base implements FilterInterface {

  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['name'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Filter by typing the supporter name'),
      '#maxlength'     => 255,
      '#size'          => 40,
      '#default_value' => isset($values['name']) ? $values['name'] : NULL,
    );
  }

  public function title() { return t('Filter by typing the supporter name'); }

  public function apply($query, array $values) {
    if (!empty($values['name'])) {
      $search = preg_replace('/[[:blank:]]+/', '%', $values['name']);
      $search = preg_replace('/%%+/', '%', $search);
      $search = preg_replace('/^%/', '', $search);
      $search = preg_replace('/%$/', '', $search);
      $query->where(
        '   LOWER(r.first_name)  LIKE :search_string ' .
        'OR LOWER(r.middle_name) LIKE :search_string ' .
        'OR LOWER(r.last_name)   LIKE :search_string ',
        array( ':search_string' => '%' . strtolower($search) . '%')
      );
    }
  }
  public function defaults() {
    return array('name' => '');
  }
}

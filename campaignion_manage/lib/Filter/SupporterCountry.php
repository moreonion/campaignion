<?php

namespace Drupal\campaignion_manage\Filter;

class SupporterCountry implements FilterInterface {
  protected $query;

  public function __construct(\SelectQueryInterface $query) {
    $this->query = $query;
  }

  protected function getOptions() {
    $query = clone $this->query;
    $query->innerJoin('field_data_field_address', 'ctr', "r.contact_id = ctr.entity_id AND ctr.entity_type = 'redhen_contact'");
    $fields =& $query->getFields();
    $fields = array();
    $query->fields('ctr', array('field_address_country'));
    $query->groupBy('ctr.field_address_country');

    $countries_in_use = $query->execute()->fetchCol();
    $countries_in_use = array_flip($countries_in_use);

    return array_intersect_key(country_get_list(), $countries_in_use);
  }

  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['country'] = array(
      '#type'          => 'select',
      '#title'         => t('Country'),
      '#options'       => $this->getOptions(),
      '#default_value' => isset($values) ? $values : NULL,
    );
    $form['#attributes']['class'][] = 'campaignion-manage-filter-country';
  }

  public function title() { return t('Is from'); }

  public function apply($query, array $values) {
    $inner = db_select('field_data_field_address', 'ctr')
      ->fields('ctr', array('entity_id'))
      ->condition('ctr.entity_type', 'redhen_contact')
      ->condition('ctr.field_address_country', $values['country']);
    $query->getQuery()->condition('r.contact_id', $inner, 'IN');
  }

  public function nrOfInstances() { return 1; }

  public function isApplicable() { return count($this->getOptions()) > 0; }
}

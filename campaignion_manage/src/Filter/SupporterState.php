<?php

namespace Drupal\campaignion_manage\Filter;

/**
 * Filter contacts by redhen_contact.redhen_state.
 */
class SupporterState extends Base implements FilterInterface {

  /**
   * {@inheritdoc}
   */
  public function defaults() {
    return ['value' => 1];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(array &$element, array &$form_state, array &$values) {
    $element['value'] = [
      '#type' => 'checkbox',
      '#title' => t('Exclude archived contacts.'),
      '#default_value' => !empty($values['value']),
      '#disabled' => TRUE,
    ];
  }

  /**
   * Return the title of this filter used in the filter listing.
   */
  public function title() {
    return t('Exclude archived supporters');
  }

  /**
   * Apply the filter to a query.
   */
  public function apply($query, array $values) {
    if (!empty($values['value'])) {
      $query->condition('r.redhen_state', 1);
    }
  }

}

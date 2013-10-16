<?php

/**
 * Simple filter to handle equal to / not equal to filters
 *
 * @ingroup views_filter_handlers
 */
class views_handler_filter_activity_type extends views_handler_filter_equality {

  /**
   * Provide a select list for value selection.
   */
  function value_form(&$form, &$form_state) {
    parent::value_form($form, $form_state);

    $options_list = array('All' => '- Any -');
    $options_list += db_query(
      'SELECT DISTINCT(type) ' .
      '  FROM {campaignion_activity} '
    )->fetchAllKeyed(0, 0);

    $form['value'] = array(
      '#type'          => 'select',
      '#title'         => t('Activity Type'),
      '#options'       => $options_list,
      '#required'      => FALSE,
    ) + $form['value'];

    unset($form['value']['#size']);
  }
}
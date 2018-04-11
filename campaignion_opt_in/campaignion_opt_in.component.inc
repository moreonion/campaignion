<?php

/**
 * @file
 * Campaignion newsletter component for webform.
 */

/**
 * Implements _webform_defaults_component().
 */
function _webform_defaults_opt_in() {
  return array(
    'name' => t('Newsletter Subscription'),
    'form_key' => 'newsletter',
    'mandatory' => 0,
    'required' => 0,
    'pid' => 0,
    'weight' => 0,
    'value' => serialize(['']),
    'extra' => array(
      'items' => '',
      'multiple' => NULL,
      'aslist' => NULL,
      'description' => '',
      'private' => FALSE,
      'title_display' => 'none',
      'display' => 'checkbox',
      'radio_labels' => [t('No'), t('Yes, I want to subscribe to the newsletter')],
      'checkbox_label' => t('Subscribe me to the newsletter.'),
      'optin_statement' => '',
      'no_is_optout' => FALSE,
    ),
  );
}

/**
 * Implements _webform_edit_component().
 */
function _webform_edit_opt_in($component) {
  $component['value'] = unserialize($component['value']);
  $newsletters = db_select('campaignion_opt_ins_lists', 'l')
    ->fields('l', array('list_id', 'title'))
    ->execute()
    ->fetchAllKeyed();

  $form['behavior'] = [
    '#type' => 'fieldset',
    '#title' => t('Behavior'),
  ];

  $form['extra']['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Description'),
    '#default_value' => $component['extra']['description'],
    '#weight' => 0,
    '#parents' => ['extra', 'description'],
  );

  $form['behavior']['value'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Default state'),
    '#options' => array('subscribed' => t('The newsletter checkbox is checked by default, the radio default is set to “Yes”.')),
    '#default_value' => $component['value'],
    '#element_validate' => array('_webform_value_validate_opt_in'),
    '#parents' => ['value'],
  );

  $display_id = drupal_html_id('newsletter-display');
  $form['extra']['display'] = [
    '#type' => 'select',
    '#title' => t('Display the newsletter opt-in as …'),
    '#options' => array(
      'checkbox' => t('Checkbox'),
      'radios' => t('Radios'),
    ),
    '#default_value' => $component['extra']['display'],
    '#id' => $display_id,
    '#parents' => ['extra', 'display'],
  ];

  $form['extra']['checkbox_label'] = [
    '#type' => 'textfield',
    '#title' => t('Label for the checkbox'),
    '#default_value' => $component['extra']['checkbox_label'],
    '#states' => ['visible' => ["#$display_id" => ['value' => 'checkbox']]],
    '#parents' => ['extra', 'checkbox_label'],
  ];

  $form['extra']['radio_labels'] = [
    // Needed for form_builder.
    '#tree' => TRUE,
    '#type' => 'fieldset',
    '#title' => t('Labels for the radios.'),
    '#states' => ['visible' => ["#$display_id" => ['value' => 'radios']]],
    1 => [
      '#type' => 'textfield',
      '#title' => t('Yes'),
      '#default_value' => $component['extra']['radio_labels'][1],
    ],
    0 => [
      '#type' => 'textfield',
      '#title' => t('No'),
      '#default_value' => $component['extra']['radio_labels'][0],
    ],
    '#parents' => ['extra', 'radio_labels'],
  ];

  $form['extra']['optin_statement'] = [
    '#type' => 'textarea',
    '#title' => t('Opt-in statement'),
    '#description' => t('This opt-in statement will be recorded as part of the supporter record in Campaignion, so that you have a clear history of what the supporter has signed up to and when. Make sure it matches the visible text in the form!'),
    '#default_value' => $component['extra']['optin_statement'],
    '#parents' => ['extra', 'optin_statement'],
  ];

  $optout_id = drupal_html_id('newsletter-no-is-optout');
  $form['behavior']['no_is_optout'] = [
    '#type' => 'checkbox',
    '#title' => t('No (radio) is taken as opt-out'),
    '#default_value' => !empty($component['extra']['no_is_optout']),
    '#id' => $optout_id,
    '#parents' => ['extra', 'no_is_optout'],
    '#states' => ['visible' => ["#$display_id" => ['value' => 'radios']]],
  ];

  return $form;
}

/**
 * Validate function.
 */
function _webform_value_validate_opt_in(&$element, &$form_state) {
  $values = &drupal_array_get_nested_value($form_state['values'], $element['#parents']);
  $values = serialize($values);
}

/**
 * Implements _webform_render_component().
 */
function _webform_render_opt_in($component, $value = NULL, $filter = TRUE) {
  $component['value'] = unserialize($component['value']);

  $element = array(
    '#required' => !empty($component['mandatory']) || !empty($component['required']),
    '#weight' => $component['weight'],
    '#theme_wrappers' => array('webform_element'),
    '#pre_render' => array(),
    '#title' => $component['name'],
    '#title_display' => $component['extra']['title_display'],
    '#description' => $component['extra']['description'],
    '#translatable' => array('title', 'description', 'options'),
  );

  switch ($component['extra']['display']) {
    case 'checkbox':
      $options['subscribed'] = $component['extra']['checkbox_label'];
      $element += [
        '#type' => 'checkboxes',
        '#default_value' => $component['value'],
        '#options' => $options,
      ];
      break;

    case 'radios':
      $l = $component['extra']['radio_labels'];
      $no_value = !empty($component['extra']['no_is_optout']) ? 'no' : '';
      $options = ['yes' => $l[1], $no_value => $l[0]];
      $element += [
        '#type' => 'radios',
        '#default_value' => !empty($component['value']['subscribed']) ? 'yes' : NULL,
        '#options' => $options,
      ];
  }

  return $element;
}

/**
 * Implements _webform_submit_COMPONENT().
 */
function _webform_submit_opt_in($component, $value) {
  switch ($component['extra']['display']) {
    case 'checkbox':
      return empty($value['subscribed']) ? [''] : ['subscribed'];

    case 'radios':
      switch ($value) {
        case 'yes':
          return ['subscribed'];
        case 'no':
          return ['unsubscribed'];
        default:
          return [''];
      }
  }
}

/**
 * Helper function to create display labels for stored values.
 */
function _webform_opt_in_value_to_label($value) {
  if (!$value) {
    return t('no change');
  }
  $value = reset($value);
  switch ($value) {
    case 'subscribed':
      return t('subscribed');
    case 'unsubscribed':
      return t('unsubscribed');
    default:
      return t('no change');
  }
}

/**
 * Implements _webform_display_component().
 */
function _webform_display_opt_in($component, $value, $format = 'html') {
  $v['#markup'] = _webform_opt_in_value_to_label($value);
  return $v;
}

/**
 * Implements _webform_CALLBACK_TYPE().
 *
 * Implements _webform_form_builder_map_TYPE().
 *
 * This hook allows us to extend the list of properties defined in
 * hook_form_builder_element_types() specific for form_builder_webform.
 */
function _webform_form_builder_map_opt_in() {
  $map['form_builder_type'] = 'opt_in';
  return $map;
}

/**
 * Implements _webform_table_component().
 */
function _webform_table_opt_in($component, $value) {
  return _webform_opt_in_value_to_label($value);
}

/**
 * Implements _webform_csv_headers_component().
 */
function _webform_csv_headers_opt_in($component, $export_options) {
  $header = array();
  $header[0] = '';
  $header[1] = '';
  $header[2] = $component['name'];
  return $header;
}

/**
 * Implements _webform_csv_data_component().
 */
function _webform_csv_data_opt_in($component, $export_options, $value) {
  return _webform_opt_in_value_to_label($value);
}

/**
 * Webform conditional comparison callback for 'equal'.
 */
function _webform_conditional_comparison_opt_in_equal(array $input_values, $rule_value, array $component) {
  if (!$input_values) {
    return $rule_value == '';
  }
  $look_for_map = [
    '' => [0, ''],
    'yes' => ['subscribed', 'yes'],
    'no' => ['unsubscribed', 'no'],
  ];
  $look_for = $look_for_map[$rule_value];
  foreach ($input_values as $value) {
    if (in_array($value, $look_for, TRUE)) {
      return TRUE;
    }
  }
  return FALSE;
}

/**
 * Webform conditional comparison callback for 'not_equal'.
 */
function _webform_conditional_comparison_opt_in_not_equal(array $input_values, $rule_value, array $component) {
  return !_webform_conditional_comparison_opt_in_equal($input_values, $rule_value, $component);
}

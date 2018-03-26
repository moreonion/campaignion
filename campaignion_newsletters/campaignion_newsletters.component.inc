<?php

/**
 * @file
 * Campaignion newsletter component for webform.
 */

/**
 * Implements _webform_defaults_component().
 */
function _webform_defaults_newsletter() {
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
      'lists' => array(),
      'opt_in_implied' => 1,
      'send_welcome' => 0,
      'title_display' => 'none',
      'display' => 'checkbox',
      'radio_labels' => [t('No'), t('Yes, I want to subscribe to the newsletter')],
      'checkbox_label' => t('Subscribe me to the newsletter.'),
      'optin_statement' => '',
      'no_is_optout' => FALSE,
      'optout_all_lists' => TRUE,
    ),
  );
}

/**
 * Implements _webform_theme_component().
 */
function _webform_theme_newsletter() {
  return array(
    'webform_display_newsletters_subscription' => array(
      'render element' => 'element',
    ),
  );
}

/**
 * Implements _webform_edit_component().
 */
function _webform_edit_newsletter($component) {
  $component['value'] = unserialize($component['value']);
  $newsletters = db_select('campaignion_newsletters_lists', 'l')
    ->fields('l', array('list_id', 'title'))
    ->execute()
    ->fetchAllKeyed();

  $form['extra']['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Description'),
    '#default_value' => $component['extra']['description'],
    '#weight' => 0,
  );

  $form['value'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Default state'),
    '#options' => array('subscribed' => t('The newsletter checkbox is checked by default.')),
    '#default_value' => $component['value'],
    '#element_validate' => array('_webform_value_validate_newsletter'),
  );

  natcasesort($newsletters);
  $form['extra']['lists'] = array(
    '#type' => module_exists('select2') && count($newsletters) > 5 ? 'select' : 'checkboxes',
    '#title' => t('Newsletter'),
    '#default_value' => $component['extra']['lists'],
    '#description' => t('Which lists should the user be subscribed to?'),
    '#options' => $newsletters,
    '#weight' => 1,
    // Only relevant if type is 'select':
    '#empty_option' => t('Select newsletters'),
    '#multiple' => 1,
    '#select2' => array(),
  );

  $form['extra']['opt_in_implied'] = array(
    '#type' => 'radios',
    '#title' => t('Double opt-in'),
    '#default_value' => $component['extra']['opt_in_implied'],
    '#options' => array(
      0 => t("Newsletter provider: This form doesn't provide double-opt-in functionality the newsletter provider should take care of it."),
      1 => t('Included in this form: This form includes a double-opt-in process no further action is needed'),
    ),
  );

  $form['extra']['send_welcome'] = array(
    '#type' => 'checkbox',
    '#title' => t('Send a welcome email (for new subscribers).'),
    '#default_value' => !empty($component['extra']['send_welcome']),
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
  ];

  $form['extra']['checkbox_label'] = [
    '#type' => 'textfield',
    '#title' => t('Label for the checkbox'),
    '#default_value' => $component['extra']['checkbox_label'],
    '#states' => ['visible' => ["#$display_id" => ['value' => 'checkbox']]],
  ];

  $form['extra']['radio_labels'] = [
    // Needed for form_builder.
    '#tree' => TRUE,
    '#type' => 'fieldset',
    '#title' => t('Labels for the radios.'),
    '#states' => ['visible' => ["#$display_id" => ['value' => 'radios']]],
    1 => [
      '#type' => 'textfield',
      '#title' => t('Subscribe'),
      '#default_value' => $component['extra']['radio_labels'][1],
    ],
    0 => [
      '#type' => 'textfield',
      '#title' => t('Unsubscribe'),
      '#default_value' => $component['extra']['radio_labels'][0],
    ],
  ];

  $form['extra']['optin_statement'] = [
    '#type' => 'textarea',
    '#title' => t('Opt-in statement'),
    '#default_value' => $component['extra']['optin_statement'],
  ];

  $optout_id = drupal_html_id('newsletter-no-is-optout');
  $form['extra']['no_is_optout'] = [
    '#type' => 'checkbox',
    '#title' => t('No (radio) is taken as opt-out'),
    '#default_value' => !empty($component['extra']['no_is_optout']),
    '#id' => $optout_id,
  ];

  $form['extra']['optout_all_lists'] = [
    '#type' => 'checkbox',
    '#title' => t('On opt-out: Remove all subscriptions for this email address.'),
    '#default_value' => !empty($component['extra']['optout_all_lists']),
    '#states' => ['visible' => ["#$optout_id" => ['checked' => TRUE]]],
  ];

  return $form;
}

/**
 * Validate function.
 */
function _webform_value_validate_newsletter(&$element, &$form_state) {
  $values = &drupal_array_get_nested_value($form_state['values'], $element['#parents']);
  $values = serialize($values);
}

/**
 * Implements _webform_render_component().
 */
function _webform_render_newsletter($component, $value = NULL, $filter = TRUE) {
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
function _webform_submit_newsletter($component, $value) {
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
function _webform_newsletter_value_to_label($value) {
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
function _webform_display_newsletter($component, $value, $format = 'html') {
  $v['#markup'] = _webform_newsletter_value_to_label($value);
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
function _webform_form_builder_map_newsletter() {
  $map['form_builder_type'] = 'newsletter';
  return $map;
}

/**
 * Implements _webform_table_component().
 */
function _webform_table_newsletter($component, $value) {
  return _webform_newsletter_value_to_label($value);
}

/**
 * Implements _webform_csv_headers_component().
 */
function _webform_csv_headers_newsletter($component, $export_options) {
  $header = array();
  $header[0] = '';
  $header[1] = '';
  $header[2] = $component['name'];
  return $header;
}

/**
 * Implements _webform_csv_data_component().
 */
function _webform_csv_data_newsletter($component, $export_options, $value) {
  return _webform_newsletter_value_to_label($value);
}

/**
 * Webform conditional comparison callback for 'equal'.
 */
function _webform_conditional_comparison_newsletter_equal(array $input_values, $rule_value, array $component) {
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
function _webform_conditional_comparison_newsletter_not_equal(array $input_values, $rule_value, array $component) {
  return !_webform_conditional_comparison_newsletter_equal($input_values, $rule_value, $component);
}

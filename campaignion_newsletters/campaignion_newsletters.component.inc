<?php

/**
 * @file
 * Campaignion newsletter component for webform.
 */

use Drupal\campaignion_newsletters\ValuePrefix;

/**
 * Implements _webform_defaults_component().
 */
function _webform_defaults_newsletter() {
  return [
    'name' => t('Newsletter Subscription'),
    'form_key' => 'newsletter',
    'mandatory' => 0,
    'required' => 0,
    'pid' => 0,
    'weight' => 0,
    'value' => serialize(['']),
    'extra' => [
      'items' => '',
      'multiple' => NULL,
      'aslist' => NULL,
      'description' => '',
      'private' => FALSE,
      'lists' => [],
      'opt_in_implied' => 1,
      'send_welcome' => 0,
      'title_display' => 'none',
      'display' => 'checkbox',
      'radio_labels' => [t('No'), t('Yes, I want to subscribe to the newsletter')],
      'checkbox_label' => t('Subscribe me to the newsletter.'),
      'optin_statement' => '',
      'no_is_optout' => FALSE,
      'optout_all_lists' => TRUE,
    ],
  ];
}

/**
 * Implements _webform_theme_component().
 */
function _webform_theme_newsletter() {
  return [
    'webform_display_newsletters_subscription' => [
      'render element' => 'element',
    ],
  ];
}

/**
 * Implements _webform_edit_component().
 */
function _webform_edit_newsletter($component) {
  $component['value'] = unserialize($component['value']);
  $newsletters = db_select('campaignion_newsletters_lists', 'l')
    ->fields('l', ['list_id', 'title'])
    ->execute()
    ->fetchAllKeyed();

  $form['behavior'] = [
    '#type' => 'fieldset',
    '#title' => t('Behavior'),
  ];

  $form['extra']['description'] = [
    '#type' => 'textarea',
    '#title' => t('Description'),
    '#default_value' => $component['extra']['description'],
    '#weight' => 0,
    '#parents' => ['extra', 'description'],
  ];

  $form['behavior']['value'] = [
    '#type' => 'checkboxes',
    '#title' => t('Default state'),
    '#options' => ['subscribed' => t('The newsletter checkbox is checked by default, the radio default is set to “Yes”.')],
    '#default_value' => $component['value'],
    '#element_validate' => ['_webform_value_validate_newsletter'],
    '#parents' => ['value'],
  ];

  $form['list_management'] = [
    '#type' => 'fieldset',
    '#title' => t('List management'),
  ];

  natcasesort($newsletters);
  $form['list_management']['lists'] = [
    '#type' => module_exists('select2') && count($newsletters) > 5 ? 'select' : 'checkboxes',
    '#title' => t('Newsletter'),
    '#default_value' => $component['extra']['lists'],
    '#description' => t('Which lists should the user be subscribed to?'),
    '#options' => $newsletters,
    // Only relevant if type is 'select':
    '#empty_option' => t('Select newsletters'),
    '#multiple' => 1,
    '#select2' => [],
    '#parents' => ['extra', 'lists'],
  ];

  $form['list_management']['opt_in_implied'] = [
    '#type' => 'radios',
    '#title' => t('Double opt-in'),
    '#default_value' => $component['extra']['opt_in_implied'],
    '#options' => [
      0 => t("Newsletter provider: This form doesn't provide double-opt-in functionality the newsletter provider should take care of it."),
      1 => t('Included in this form: This form includes a double-opt-in process no further action is needed'),
    ],
    '#parents' => ['extra', 'opt_in_implied'],
  ];

  $form['list_management']['welcome'] = [
    '#type' => 'container',
    '#attributes' => ['class' => ['form-item']],
  ];
  $form['list_management']['welcome']['label'] = [
    '#theme' => 'form_element_label',
    '#title' => t('Welcome email'),
    '#title_display' => 'before',
  ];
  $form['list_management']['welcome']['enabled'] = [
    '#type' => 'checkbox',
    '#title' => t('Send a welcome email (for new subscribers).'),
    '#default_value' => !empty($component['extra']['send_welcome']),
    '#parents' => ['extra', 'send_welcome'],
  ];

  $display_id = drupal_html_id('newsletter-display');
  $form['extra']['display'] = [
    '#type' => 'select',
    '#title' => t('Display the newsletter opt-in as …'),
    '#options' => [
      'checkbox' => t('Checkbox'),
      'radios' => t('Radios'),
    ],
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

  $form['behavior']['optout_all_lists'] = [
    '#type' => 'checkbox',
    '#title' => t('If the email address is unsubscribed it will be unsubscribed from all email lists.'),
    '#default_value' => !empty($component['extra']['optout_all_lists']),
    '#states' => [
      'visible' => [
        "#$optout_id" => ['checked' => TRUE],
        "#$display_id" => ['value' => 'radios'],
      ],
    ],
    '#parents' => ['extra', 'optout_all_lists'],
  ];

  return $form;
}

/**
 * Implements _webform_render_component().
 */
function _webform_render_newsletter($component, $value = NULL, $filter = TRUE) {
  $component['value'] = unserialize($component['value']);

  $element = [
    '#required' => !empty($component['mandatory']) || !empty($component['required']),
    '#weight' => $component['weight'],
    '#theme_wrappers' => ['webform_element'],
    '#pre_render' => [],
    '#title' => $component['name'],
    '#title_display' => $component['extra']['title_display'],
    '#description' => $component['extra']['description'],
    '#translatable' => ['title', 'description', 'options'],
  ];

  switch ($component['extra']['display']) {
    case 'checkbox':
      $options['opt-in'] = $component['extra']['checkbox_label'];
      $element += [
        '#type' => 'checkboxes',
        '#default_value' => [
          'opt-in' => ValuePrefix::remove($component['value']),
        ],
        '#options' => $options,
      ];
      break;

    case 'radios':
      $l = $component['extra']['radio_labels'];
      $no_value = !empty($component['extra']['no_is_optout']) ? 'opt-out' : 'no-change';
      $options = ['opt-in' => $l[1], $no_value => $l[0]];
      $element += [
        '#type' => 'radios',
        '#default_value' => ValuePrefix::remove($component['value']),
        '#options' => $options,
      ];
  }

  return $element;
}

/**
 * Implements _webform_submit_COMPONENT().
 */
function _webform_submit_newsletter($component, $value) {
  return [ValuePrefix::add($value, $component['extra']['display'])];
}

/**
 * Helper function to create display labels for stored values.
 */
function _webform_newsletter_value_to_label($value) {
  if (!$value) {
    return t('Unknown value');
  }
  $value = reset($value);
  switch ($value) {
    case 'checkbox:opt-in':
      return t('Checkbox opt-in');

    case 'checkbox:no-change':
      return t('Checkbox no change');

    case 'radios:opt-in':
      return t('Radio opt-in');

    case 'radios:no-change':
      return t('Radio no change');

    case 'radios:opt-out':
      return t('Radio opt-out');

    case 'radios:not-selected':
      return t('Radio not selected (no change)');

    default:
      return t('Unknown value');
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
  $header = [];
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
  $type = $component['extra']['display'];
  if (!$input_values) {
    return $rule_value == $type . ':not-selected';
  }
  foreach ($input_values as $value) {
    if (strpos($value, $type) !== 0) {
      $value = ValuePrefix::add($value, $type);
    }
    return $rule_value == $value;
  }
  return FALSE;
}

/**
 * Webform conditional comparison callback for 'not_equal'.
 */
function _webform_conditional_comparison_newsletter_not_equal(array $input_values, $rule_value, array $component) {
  return !_webform_conditional_comparison_newsletter_equal($input_values, $rule_value, $component);
}

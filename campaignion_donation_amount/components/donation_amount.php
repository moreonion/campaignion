<?php

/**
 * @file
 * Donation amount webform component.
 */

/**
 * Implements _webform_defaults_component().
 */
function _webform_defaults_donation_amount() {
  $defaults = webform_component_invoke('number', 'defaults');
  unset($defaults['type']);
  $defaults['name'] = t('Donation amount');
  $defaults['extra'] += [
    'currency' => 'XXX',
    'options' => [],
    'other_option' => TRUE,
    'other_text' => t('Other'),
  ];
  return $defaults;
}

/**
 * Fix the view field(s) that are automatically generated for number components.
 */
function _webform_view_field_donation_amounts($component, $fields) {
  foreach ($fields as &$field) {
    $field['webform_datatype'] = 'number';
  }
  return $fields;
}

/**
 * Implements _webform_edit_component().
 */
function _webform_edit_donation_amount($component) {
  $number_component = $component;
  $component['extra']['type'] = 'textfield';
  $form = webform_component_invoke('number', 'edit', $component);
  unset($form['display']['type']);
  $form['behavior'] = [
    '#type' => 'fieldset',
    '#title' => t('Behavior'),
  ];
  $form['behavior']['currency'] = [
    '#type' => 'select',
    '#title' => t('Currency'),
    '#options' => webform_currency_options(),
    '#default_value' => $component['extra']['currency'],
    '#parents' => ['extra', 'currency'],
  ];
  $form['behavior']['options'] = [
    '#type' => 'textarea',
    '#title' => t('Pre-defined amounts'),
    '#description' => t('Enter pre-defined donation amounts separated by a line break. Use “.” as decimal point.'),
    '#default_value' => implode("\n", $component['extra']['options']),
    '#parents' => ['extra', 'options'],
    '#element_validate' => ['_webform_edit_donation_amount_validate_amounts'],
  ];
  $form['behavior']['other_option'] = [
    '#type' => 'checkbox',
    '#title' => t('Allow "Other..." option'),
    '#default_value' => $component['extra']['other_option'],
    '#description' => t('Check this option if you want to allow users to enter an option not on the list.'),
    '#parents' => ['extra', 'other_option'],
    '#attributes' => ['class' => ['other-option-checkbox']],
  ];
  $form['behavior']['other_text'] = [
    '#type' => 'textfield',
    '#title' => t('Text for "Other..." option'),
    '#default_value' => $component['extra']['other_text'],
    '#description' => t('If allowing other options, enter text to be used for other-enabling option.'),
    '#parents' => ['extra', 'other_text'],
    '#weight' => 3,
    '#states' => [
      'visible' => [
        ':input.other-option-checkbox' => ['checked' => TRUE],
      ],
    ],
  ];

  return $form;
}

/**
 * Element validate callback: Split amount array.
 */
function _webform_edit_donation_amount_validate_amounts($element, &$form_state) {
  $validate_number = function ($value) use ($element, $form_state) {
    $component = $form_state['values'];
    $element_stub = [
      '#title' => $element['#title'],
      '#min' => $component['extra']['min'],
      '#max' => $component['extra']['max'],
      '#step' => $component['extra']['step'] ? abs($component['extra']['step']) : '',
      '#integer' => $component['extra']['integer'],
      '#point' => '.',
      '#separator' => ',',
      '#decimals' => $component['extra']['decimals'],
      '#parents' => ['number'],
      '#value' => $value,
    ];
    $fs['values'] = [];

    // Backup $form_state and $_SESSION['messages'] and restore them afterwards.
    $messages = $_SESSION['messages'] ?? [];
    $errors = &drupal_static('form_set_error', []);
    $original_errors = $errors;
    $errors = [];
    _webform_validate_number($element_stub, $fs);
    $our_errors = $errors;
    $errors = $original_errors;
    $_SESSION['messages'] = $messages;

    if ($our_errors) {
      return FALSE;
    }
    return webform_number_standardize($value, $element_stub['#point']);
  };

  $only_numeric = TRUE;
  $values = [];
  foreach (array_filter(array_map('trim', explode("\n", $element['#value']))) as $value) {
    if (($number = $validate_number($value)) !== FALSE) {
      $values[] = $number;
    }
    else {
      $only_numeric = FALSE;
      break;
    }
  }
  if (!$only_numeric) {
    form_error($element, t('Only valid numeric values are allowed (see validation settings).'));
    return;
  }
  form_set_value($element, $values, $form_state);
}

/**
 * Implements _webform_render_component().
 */
function _webform_render_donation_amount($component, $value = NULL, $filter = TRUE, $submission = NULL) {
  $number_component = $component;
  $component['extra']['type'] = 'textfield';
  $component['extra']['unique'] = FALSE;
  $element = webform_component_invoke('number', 'render', $component, $value, $filter, $submission);
  $element['#attributes']['class'][] = 'donation-amount';
  $element['#attributes']['class'][] = 'donation-amount-' . $component['extra']['currency'];
  $element['#attributes']['data-currency'] = $component['extra']['currency'];
  if ($component['extra']['options']) {
    $currency = currency_load($component['extra']['currency']);
    $element['#type'] = 'select_or_other';
    $element['#select_type'] = 'radios';
    $element['#options'] = drupal_map_assoc($component['extra']['options'], function ($amount) use ($currency) {
      return $currency->sign . ' ' . $amount;
    });
    $element['#other'] = !empty($component['extra']['other_text']) ? check_plain($component['extra']['other_text']) : t('Other...');
    $element['#translatable'][] = 'other';
    $element['#other_title'] = $currency->sign;
    $element['#other_title_display'] = 'before';
    $element['#other_unknown_defaults'] = 'other';
    $element['#other_delimiter'] = ', ';
    // Merge in Webform's #process function for Select or other.
    $element['#process'] = array_merge(element_info_property('select_or_other', '#process'), ['webform_expand_select_or_other']);
  }
  return $element;
}

/**
 * Implements _webform_display_component().
 */
function _webform_display_donation_amount($component, $value, $format = 'html', $submission = array()) {
  return webform_component_invoke('number', 'display', $component, $value, $format, $submission);
}

/**
 * Implements _webform_analysis_component().
 */
function _webform_analysis_donation_amount($component, $sids = array(), $single = FALSE, $join = NULL) {
  return webform_component_invoke('number', 'analysis', $component, $sids, $single, $join);
}

/**
 * Implements _webform_table_component().
 */
function _webform_table_donation_amount($component, $value) {
  return webform_component_invoke('number', 'table', $component, $value);
}

/**
 * Implements _webform_action_set_component().
 */
function _webform_action_set_donation_amount($component, &$element, &$form_state, $value) {
  $element['#value'] = $value;
  form_set_value($element, $value, $form_state);
}

/**
 * Implements _webform_csv_headers_component().
 */
function _webform_csv_headers_donation_amount($component, $export_options) {
  return webform_component_invoke('number', 'csv_headers', $component, $export_options);
}

/**
 * Implements _webform_csv_data_component().
 */
function _webform_csv_data_donation_amount($component, $export_options, $value) {
  return webform_component_invoke('number', 'csv_data', $component, $export_options, $value);
}

/**
 * Implements _webform_submit_component().
 */
function _webform_submit_donation_amount($component, $value) {
  return webform_component_invoke('number', 'submit', $component, $value);
}

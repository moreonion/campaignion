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
    'value' => serialize(array('subscribed' => 'subscribed')),
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
    // only relevant if type is 'select':
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
    '#tree' => TRUE, // Needed for form_builder.
    '#type' => 'fieldset',
    '#title' => t('Labels for the radios'),
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
  ];

  return $form;
}

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
      $options = ['yes' => $l[1], 'no' => $l[0]];
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
      break;

    case 'radios':
      return $value == 'yes' ? ['subscribed'] : [''];
      break;
  }
}

/**
 * Implements _webform_display_component().
 */
function _webform_display_newsletter($component, $value, $format = 'html') {
  if (!empty($value['subscribed'])) {
    return array('#markup' => t('subscribed'));
  }
  else {
    return array('#markup' => t('not subscribed'));
  }
}

/**
 * Form callback for the newsletter property.
 *
 * @see _webform_form_builder_map_newsletter().
 */
function campaignion_newsletters_form_builder_property_newsletter_form(&$form_state, $form_type, $element, $property) {
  $form['options'] = array(
    '#form_builder' => array('property_group' => 'options'),
    '#tree' => TRUE,
  );
  $edit = _webform_edit_newsletter($element['#webform_component']);
  $form['options']['lists'] = $edit['extra']['lists'];
  $form['newsletter_title'] = $edit['extra']['description'];
  $form['newsletter_value'] = $edit['value'];
  $form['newsletter_opt_in_implied'] = $edit['extra']['opt_in_implied'];
  $form['newsletter_send_welcome'] = $edit['extra']['send_welcome'];
  $dp['#form_builder']['property_group'] = 'display';
  $form['newsletter_display'] = $edit['extra']['display'] + $dp;
  $form['newsletter_checkbox_label'] = $edit['extra']['checkbox_label'] + $dp;
  $form['newsletter_radio_labels'] = $edit['extra']['radio_labels'] + $dp;

  return $form;
}

/**
 * Submit handler for the newsletter property.
 *
 * @see _webform_form_builder_map_newsletter().
 */
function campaignion_newsletters_form_builder_property_newsletter_form_submit(&$form, &$form_state) {
  $form_state['values']['newsletter_lists'] = $form_state['values']['options']['lists'];
}


/**
 * Implements _webform_form_builder_properties_component().
 *
 * Component specific properties.
 * This is currently broken as the component specific properties are merged into
 * the global property list. That makes it behave the same way as an implementation
 * of hook_form_builder_properties().
 *
 * @see form_builder_webform_form_builder_properties().
 */
function _webform_form_builder_properties_newsletter() {
  return array(
    'newsletter_lists' => array(
      'form' => 'campaignion_newsletters_form_builder_property_newsletter_form',
      'submit' => array('campaignion_newsletters_form_builder_property_newsletter_form_submit'),
    ),
    'newsletter_title' => array(),
    'newsletter_opt_in_implied' => array(),
    'newsletter_send_welcome' => [],
    'newsletter_display' => [],
    'newsletter_radio_labels' => [],
    'newsletter_checkbox_label' => [],
  );
}

/**
 * Implements _webform_CALLBACK_TYPE().
 * Implements _webform_form_builder_map_TYPE().
 *
 * Defines mapping from webform component to form_builder element type.
 * Tell form_builder_webform how to store properties:
 * $form_state['values']['newsletter'] -> $component['value'].
 *
 * This hook allows us to extend the list of properties defined in
 * hook_form_builder_element_types() specific for form_builder_webform.
 */
function _webform_form_builder_map_newsletter() {
  $map['form_builder_type'] = 'newsletter';
  $map['properties'] = array(
    'newsletter_lists' => array(
      'storage_parents' => array('extra', 'lists'),
    ),
    'newsletter_title' => array(
      'storage_parents' => array('extra', 'description'),
    ),
    'newsletter_opt_in_implied' => array(
      'storage_parents' => array('extra', 'opt_in_implied'),
    ),
    'newsletter_send_welcome' => array(
      'storage_parents' => array('extra', 'send_welcome'),
    ),
    'newsletter_value' => array(
      'storage_parents' => array('value'),
    ),
    'newsletter_display' => [
      'storage_parents' => ['extra', 'display'],
    ],
    'newsletter_radio_labels' => [
      'storage_parents' => ['extra', 'radio_labels'],
    ],
    'newsletter_checkbox_label' => [
      'storage_parents' => ['extra', 'checkbox_label'],
    ],
  );
  return $map;
}

/**
 * Implements _webform_table_component().
 */
function _webform_table_newsletter($component, $value) {
  $v = empty($value['subscribed']) ? t('not subscribed') : t('subscribed');
  return $v;
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
  $v = t('not subscribed');
  # value of field 'subscribed' set to 'subscribed' if checkbox checked,
  # '0' otherwise
  if (!empty($value['subscribed'])) {
    $v = t('subscribed');
  }
  return $v;
}


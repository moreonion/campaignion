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
    'pid' => 0,
    'weight' => 0,
    'value' => serialize(array('subscribed' => 'subscribed')),
    'extra' => array(
      'items' => '',
      'multiple' => NULL,
      'aslist' => NULL,
      'description' => t('Subscribe me to the newsletter.'),
      'private' => FALSE,
      'lists' => array(),
      'opt_in_implied' => 1,
      'title_display' => 'none',
      'display' => 'checkbox',
      'radio_labels' => [t('No'), t('Yes, I want to subscribe to the newsletter')],
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
    '#type' => 'textfield',
    '#title' => t('Description'),
    '#default_value' => !empty($component['extra']['description']) ? $component['extra']['description'] : t('Subscribe me to the newsletter.'),
    '#description' => t('This text is displayed next to the opt-in checkbox.'),
    '#weight' => 0,
    '#required' => TRUE,
  );

  $form['value'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Default state'),
    '#options' => array('subscribed' => t('The newsletter checkbox is checked by default.')),
    '#default_value' => $component['value'],
    '#element_validate' => array('_webform_value_validate_newsletter'),
  );

  $form['extra']['lists'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Newsletter'),
    '#default_value' => $component['extra']['lists'],
    '#description' => t('Which lists should the user be subscribed to?'),
    '#options' => $newsletters,
    '#weight' => 1,
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
    '#required' => $component['mandatory'],
    '#weight' => $component['weight'],
    '#theme_wrappers' => array('webform_element'),
    '#pre_render' => array(),
    '#title' => $component['name'],
    '#title_display' => $component['extra']['title_display'],
    '#translatable' => array('title', 'description', 'options'),
  );

  switch ($component['extra']['display']) {
    case 'checkbox':
      $options['subscribed'] = $component['extra']['description'];
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
        '#default_value' => !empty($component['value']) ? 'yes' : 'no',
        '#options' => $options,
        '#element_validate' => ['_campaignion_newsletters_radio_values'],
      ];
  }

  return $element;
}

/**
 * Element validate handler for newsletter opt-in as radios.
 *
 * Transform value into whatever the checkbox would evaluate to.
 */
function _campaignion_newsletters_radio_values($element, &$form_state) {
  $value = &drupal_array_get_nested_value($form_state['values'], $element['#parents']);
  $value = ['subscribed' => $value == 'yes' ? 'subscribed' : 0];
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
  // we need to redo the mapping here because storage_parents arent resolved until
  // the form is saved for good.
  if (isset($element['#newsletter_lists'])) {
    $element['#webform_component']['extra']['lists'] = $element['#newsletter_lists'];
  }
  if (isset($element['#newsletter_title'])) {
    $element['#webform_component']['extra']['description'] = $element['#newsletter_title'];
  }
  if (isset($element['#newsletter_value'])) {
    $element['#webform_component']['value'] = $element['#newsletter_value'];
  }
  if (isset($element['#newsletter_opt_in_implied'])) {
    $element['#webform_component']['extra']['opt_in_implied'] = $element['#newsletter_opt_in_implied'];
  }
  $edit = _webform_edit_newsletter($element['#webform_component']);
  $form['options']['lists'] = $edit['extra']['lists'];
  $form['newsletter_title'] = $edit['extra']['description'];
  $form['newsletter_value'] = $edit['value'];
  $form['newsletter_opt_in_implied'] = $edit['extra']['opt_in_implied'];
  $dp['#form_builder']['property_group'] = 'display';
  $form['newsletter_display'] = $edit['extra']['display'] + $dp;
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
    'newsletter_display' => [],
    'newsletter_radio_labels' => [],
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
    'newsletter_value' => array(
      'storage_parents' => array('value'),
    ),
    'newsletter_display' => [
      'storage_parents' => ['extra', 'display'],
    ],
    'newsletter_radio_labels' => [
      'storage_parents' => ['extra', 'radio_labels'],
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


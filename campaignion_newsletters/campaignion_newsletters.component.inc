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
  // This is needed to correctly save components if they are just
  // drag-n-dropped and never edited.
  $element = array(
    '#newsletter_value' => $component['value'],
    '#newsletter_lists' => $component['extra']['lists'],
    '#newsletter_title' => $component['extra']['description'],
  );
  $component['value'] = unserialize($component['value']);

  $options['subscribed'] = $component['extra']['description'];
  $element += array(
    '#type' => 'checkboxes',
    '#default_value' => array(),
    '#required' => $component['mandatory'],
    '#weight' => $component['weight'],
    '#theme_wrappers' => array('webform_element'),
    '#pre_render' => array(),
    '#title' => $component['name'],
    '#options' => $options,
    '#default_value' => $component['value'],
    '#translatable' => array('title', 'description', 'options'),
  );

  return $element;
}

/**
 * Implements _webform_display_component().
 */
function _webform_display_newsletter($component, $value, $format = 'html') {
  if (!empty($component['value']['subscribed'])) {
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
  if (isset($element['#newsletter'])) {
    $element['#webform_component']['value'] = $element['#newsletter'];
  }
  $form['options'] += _webform_edit_newsletter($element['#webform_component']);
  unset($form['options']['name']);
  return $form;
}

function campaignion_newsletters_form_builder_property_newsletter_form_submit(&$form, &$form_state) {
  $form_state['values']['newsletter'] = $form_state['values']['options']['value'];
}


/**
 * Implements _webform_form_builder_properties_component().
 */
function _webform_form_builder_properties_newsletter() {
  return array(
    'newsletter' => array(
      'form' => 'campaignion_newsletters_form_builder_property_newsletter_form',
      'submit' => array('campaignion_newsletters_form_builder_property_newsletter_form_submit'),
    ),
  );
}

/**
 * Implements _webform_CALLBACK_TYPE().
 * Implements _webform_form_builder_map_TYPE().
 */
function _webform_form_builder_map_newsletter() {
  $map['form_builder_type'] = 'newsletter';
  $map['properties'] = array(
    'newsletter' => array(
      'form_parents' => array('options', 'value'),
      'storage_parents' => array('value'),
    ),
  );
  return $map;
}

/**
 * Implements _webform_table_component().
 */
function _webform_table_newsletter($component, $value) {
  $v = t('not subscribed');
  if (!empty($value[0]) && $value[0]) {
    $v = t('subscribed');
  }
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
  if (!empty($value[0]) && $value[0]) {
    $v = t('subscribed');
  }
  return $v;
}


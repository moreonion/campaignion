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
    'name' => 'Newsletter Subscription',
    'form_key' => 'newsletter',
    'mandatory' => 0,
    'pid' => 0,
    'weight' => 0,
    'value' => '',
    'extra' => array(
      'items' => '',
      'multiple' => NULL,
      'aslist' => NULL,
      'description' => '',
      'private' => FALSE,
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
  $newsletters = db_select('campaignion_newsletters_lists', 'l')
    ->fields('l', array('identifier', 'title'))
    ->execute()
    ->fetchAllKeyed();

  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Label'),
    '#default_value' => !empty($component['name']) ? $component['name'] : t('Subscribe me to the newsletter.'),
    '#description' => t('This text is displayed next to the opt-in checkbox.'),
    '#weight' => 0,
    '#required' => TRUE,
  );

  $form['value'] = array(
    '#type' => 'radios',
    '#title' => t('Newsletters'),
    '#default_value' => $component['value'],
    '#description' => t('Which newsletter should the user be subscribed to?'),
    '#options' => $newsletters,
    '#weight' => 1,
  );

  return $form;
}

/**
 * Implements _webform_render_component().
 */
function _webform_render_newsletter($component, $value = NULL, $filter = TRUE) {
  $element = array(
    '#type' => 'checkbox',
    '#default_value' => $value,
    '#required' => $component['mandatory'],
    '#weight' => $component['weight'],
    '#theme_wrappers' => array('webform_element'),
    // Needed to disable double-wrapping of radios and checkboxes.
    '#pre_render' => array(),
    '#translatable' => array('title', 'description', 'options'),
    '#title' => $component['name'],
    '#description' => $component['extra']['description'],
  );

  return $element;
}

/**
 * Implements _webform_display_component().
 */
function _webform_display_newsletter($component, $value, $format = 'html') {
  $list = $component['value'];
  $value = array_shift($value);
  if ($value) {
    return array('#markup' => t('User subscribed to @l', array('@l' => $list)));
  }
  else {
    return array(
      '#markup' => t('User did not subscribe to @l', array('@l' => $list)));
  }
}

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
  $form['title'] = $form['options']['name'];
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
    'title' => array(
      'form' => NULL,
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

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

  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Text'),
    '#default_value' => t('Subscribe me to the newsletter.'),
    '#description' => t('Label for the subscription newsletters.'),
    '#weight' => 0,
  );

  $form['value'] = array(
    '#type' => 'radios',
    '#title' => t('Newsletters'),
    '#default_value' => $component['value'],
    '#description' => t('Which newsletter should the user be subscribed to?'),
    '#options' => $newsletters,
    '#weight' => 0,
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
  );

  if (empty($component['extra']['description'])) {
    $element['#title'] = t('Subscribe me to the newsletter @newsletter', array('@newsletter' => $component['value']));
  }
  else {
    $element['#title'] = $component['extra']['description'];
  }

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

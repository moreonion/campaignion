<?php

/**
 * @file
 * Webform module phone number component.
 */

require_once drupal_get_path('module', 'webform') . '/components/textfield.inc';

/**
 * Implements _webform_defaults_component().
 */
function _webform_defaults_phone_number() {
  return _webform_defaults_textfield();
}

/**
 * Implements _webform_edit_component().
 */
function _webform_edit_phone_number($component) {
  $form = _webform_edit_textfield($component);
  return $form;
}

/**
 * Implements _webform_render_component().
 */
function _webform_render_phone_number($component, $value = NULL, $filter = TRUE, $submission = NULL) {
  return _webform_render_textfield($component, $value, $filter, $submission);
}

/**
 * Implements _webform_display_component().
 */
function _webform_display_phone_number($component, $value, $format = 'html', $submission = array()) {
  return _webform_display_textfield($component, $value, $format, $submission);
}

/**
 * Implements _webform_analysis_component().
 */
function _webform_analysis_phone_number($component, $sids = array(), $single = FALSE, $join = NULL) {
  return _webform_analysis_textfield($component, $sids, $single, $join);
}

/**
 * Implements _webform_table_component().
 */
function _webform_table_phone_number($component, $value) {
  return _webform_table_textfield($component, $value);
}

/**
 * Implements _webform_action_set_component().
 */
function _webform_action_set_phone_number($component, &$element, &$form_state, $value) {
  _webform_action_set_textfield($component, $element, $form_state, $value);
}

/**
 * Implements _webform_csv_headers_component().
 */
function _webform_csv_headers_phone_number($component, $export_options) {
  return _webform_csv_headers_textfield($component, $export_options);
}

/**
 * Implements _webform_csv_data_component().
 */
function _webform_csv_data_phone_number($component, $export_options, $value) {
  return _webform_csv_data_textfield($component, $export_options, $value);
}

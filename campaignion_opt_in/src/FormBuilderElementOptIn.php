<?php

namespace Drupal\campaignion_opt_in;

use Drupal\little_helpers\ArrayConfig;

/**
 * Form builder integration for the newsletter webform component.
 */
class FormBuilderElementOptIn extends \FormBuilderWebformElement {

  /**
   * {@inheritdoc}
   */
  public function configurationForm($form, &$form_state) {
    $form = parent::configurationForm($form, $form_state);
    $form['description']['#weight'] = 0;

    $component = $this->element['#webform_component'];

    // Only top-level elements can be assigned to property groups.
    // @see form_builder_field_configure_pre_render()
    $edit = _webform_edit_opt_in($component);
    $form['value'] = $edit['behavior']['value'];
    $dp['#form_builder']['property_group'] = 'display';
    $form['display'] = $edit['extra']['display'] + $dp;
    $form['checkbox_label'] = $edit['extra']['checkbox_label'] + $dp;
    $form['radio_labels'] = $edit['extra']['radio_labels'] + $dp;
    $form['optin_statement'] = $edit['extra']['optin_statement'];
    $form['no_is_optout'] = $edit['behavior']['no_is_optout'];

    return $form;
  }

  /**
   * Store component configuration just like webform would do it.
   *
   * The values are already at their proper places in `$form_state['values']`
   * because the `#parents` array is provided in `_webform_edit_opt_in()`.
   */
  public function configurationSubmit(&$form, &$form_state) {
    $component = $form_state['values'];
    ArrayConfig::mergeDefaults($component, $this->element['#webform_component']);
    $this->element['#webform_component'] = $component;
    parent::configurationSubmit($form, $form_state);
  }

}

<?php

namespace Drupal\campaignion_newsletters;

/**
 * Form builder integration for the newsletter webform component.
 */
class FormBuilderElementNewsletter extends \FormBuilderWebformElement {

  /**
   * {@inheritdoc}
   */
  public function configurationForm($form, &$form_state) {
    $form = parent::configurationForm($form, $form_state);

    $component = $this->element['#webform_component'];

    $form['#property_groups']['lists'] = [
      'title' => t('Lists'),
      'weight' => 2,
    ];

    // Only top-level elements can be assigned to property groups.
    // @see form_builder_field_configure_pre_render()
    $edit = _webform_edit_newsletter($component);
    $form['lists'] = ['#type' => NULL] + $edit['list_management'];
    $form['lists']['#form_builder']['property_group'] = 'lists';
    $form['value'] = $edit['behavior']['value'];
    $form['description'] = $edit['extra']['description'];
    $dp['#form_builder']['property_group'] = 'display';
    $form['display'] = $edit['extra']['display'] + $dp;
    $form['checkbox_label'] = $edit['extra']['checkbox_label'] + $dp;
    $form['radio_labels'] = $edit['extra']['radio_labels'] + $dp;
    $form['optin_statement'] = $edit['extra']['optin_statement'];
    $form['no_is_optout'] = $edit['behavior']['no_is_optout'];
    $form['optout_all_lists'] = $edit['behavior']['optout_all_lists'];

    return $form;
  }

}

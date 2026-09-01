<?php

namespace Drupal\campaignion_donation_amount;

use Drupal\form_builder_webform\Element;
use Drupal\little_helpers\ArrayConfig;

/**
 * Form builder integration for the newsletter webform component.
 */
class FormBuilderElementDonationAmount extends Element {

  /**
   * {@inheritdoc}
   */
  public function configurationForm($form, &$form_state) {
    $form = parent::configurationForm($form, $form_state);
    $form['description']['#weight'] = 0;

    $component = $this->element['#webform_component'];

    // Only top-level elements can be assigned to property groups.
    // @see form_builder_field_configure_pre_render()
    $edit = $this->componentEditForm($component);
    $form['currency'] = $edit['behavior']['currency'];
    $form['options'] = $edit['behavior']['options'];
    $form['other_option'] = $edit['behavior']['other_option'];
    $form['other_text'] = $edit['behavior']['other_text'];

    $group['#form_builder']['property_group'] = 'display';
    $form['decimals'] = $edit['display']['decimals'] + $group;
    $form['separator'] = $edit['display']['separator'] + $group;
    $form['point'] = $edit['display']['point'] + $group;

    $group['#form_builder']['property_group'] = 'validation';
    $form['integer'] = $edit['validation']['integer'] + $group;
    $form['min'] = $edit['validation']['min'] + $group;
    $form['max'] = $edit['validation']['max'] + $group;
    $form['step'] = $edit['validation']['step'] + $group;

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

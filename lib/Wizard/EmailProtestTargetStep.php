<?php

namespace Drupal\campaignion\Wizard;

class EmailProtestTargetStep extends WizardStep {

  protected $step  = 'target';
  protected $title = 'Target';

  public function stepForm($form, &$form_state) {

    $form = parent::stepForm($form, $form_state);

    module_load_include('inc', 'mo_utilities', 'mo_form');
    $form += \MO\Form\get_entity_field_form('node', $this->wizard->node, array('field_protest_target_options', 'field_protest_target'), $form_state);

    return $form;
  }

  public function submitStep($form, &$form_state) {

    $target_contacts = NULL;
    foreach($form_state['values']['field_protest_target']['und'] as $key => $import_field) {
      if ($key !== 'add_more' && isset($import_field['target_id'])) {
        $target_contacts[(int) $import_field['target_id']] = redhen_contact_load((int) $import_field['target_id']);
      }
    }

    $action_node_wrapper = entity_metadata_wrapper('node', $this->wizard->node);

    $action_node_wrapper->field_protest_target->set($target_contacts);
    $action_node_wrapper->field_protest_target_options->set($form_state['values']['field_protest_target_options']['und']['0']['value']);

    $action_node_wrapper->save();
  }

  public function checkDependencies() {
    return isset($this->wizard->node->nid);
  }

}


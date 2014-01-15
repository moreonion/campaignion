<?php

namespace Drupal\campaignion\Wizard;

class EmailProtestTargetStep extends WizardStep {

  protected $step  = 'target';
  protected $title = 'Target';

  public function stepForm($form, &$form_state) {

    $form = parent::stepForm($form, $form_state);

    module_load_include('inc', 'mo_utilities', 'mo_form');
    $form += \MO\Form\get_entity_field_form('node', $this->wizard->node, array('field_protest_target_options', 'field_protest_target'));

    return $form;
  }

  protected function deleteComponent($form_key, &$form_state) {
    foreach ($this->wizard->node->webform['components'] as $cid => $component) {

      if ($component['form_key'] == $form_key) {
        webform_component_delete($this->wizard->node, $component);
        unset($this->wizard->node->webform['components'][$cid]);
        break;
      }
    }
  }

  protected function insertProtestTargetComponent(&$target_contacts) {
    $target_field_cid = NULL;
    $fieldset_cid     = NULL;

    foreach ($this->wizard->node->webform['components'] as $cid => &$component) {
      if ($component['form_key'] == 'email_protest_target') {
        $target_field_cid = $cid;
        break;
      }
      elseif ($component['form_key'] == 'your_message') {
        $fieldset_cid = $cid;
      }
    }

    if ($target_field_cid == NULL) {
      $_GET['name']      = 'Send your protest to';
      $_GET['mandatory'] = 0;
      $_GET['weight']    = -1;
      $_GET['pid']       = $fieldset_cid;
      $component         = webform_menu_component_load('new', $this->wizard->node->nid, 'select');

      foreach(array('name', 'mandatory', 'weight', 'pid') as $i) {
        unset($_GET[$i]);
      }

      $component['pid']                    = $fieldset_cid;
      $component['form_key']               = 'email_protest_target';
      $component['extra']['multiple']      = FALSE;
      $component['extra']['aslist']        = TRUE;
      $component['extra']['other_option']  = TRUE;
      $component['extra']['title_display'] = 'before';
      $component['extra']['custom_keys']   = FALSE;
      $component['extra']['private']       = 0;

      $target_field_cid = webform_component_insert($component);

      $this->wizard->node->webform['components'][$target_field_cid] = $component;
    }

    $this->wizard->node->webform['components'][$target_field_cid]['extra']['items'] = '';
    foreach ($target_contacts as $contact) {
      $this->wizard->node->webform['components'][$target_field_cid]['extra']['items'] .= $contact->contact_id . "|" . $contact->first_name . " " . $contact->last_name . "\n";
    }

    webform_component_update($this->wizard->node->webform['components'][$target_field_cid]);
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

    if (isset($this->wizard->node->webform['components']) == TRUE) {
      if ($form_state['values']['field_protest_target_options']['und']['0']['value'] == 'preselect') {
        $this->deleteComponent('email_protest_target', $form_state);
      }
      else {
        $this->insertProtestTargetComponent($target_contacts);
      }
    }

    db_query('DELETE FROM form_builder_cache');
  }

  public function checkDependencies() {
    return isset($this->wizard->node->nid);
  }

}

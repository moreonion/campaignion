<?php

namespace Drupal\campaignion\Wizard;

class ContentStep extends WizardStep {
  protected $step = 'content';
  protected $title = 'Content';

  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);
    // load original node form
    form_load_include($form_state, 'inc', 'node', 'node.pages');
    $form = node_form($form, $form_state, $this->wizard->node);

    $form['field_thank_you_pages']['#access'] = FALSE;

    $form['actions']['#access'] = FALSE;
    $form['options']['#access'] = TRUE;
    unset($form['#metatags']);

    // don't publish per default
    if (!isset($this->wizard->node->nid)) {
      $form['options']['status']['#default_value'] = 0;
      $form['options']['promote']['#default_value'] = 0;
    }

    // call path and path_auto form_alter functions as they do not get called automatically
    // (they expect form_id 'node_form' but 'wizard_step_form' is provided)
    drupal_alter('node_form', $form, $form_state);

    // secondary container
    $form['wizard_secondary'] = array(
      '#type' => 'container',
      '#weight' => 3001,
    );

    $wizard_secondary_used = false;
    // move specific items to secondary container
    foreach (array('field_main_image') as $field_name) {
      if (isset($form[$field_name])) {
        $form['wizard_secondary'][$field_name] = $form[$field_name];
        unset($form[$field_name]);
        $wizard_secondary_used = true;
      }
    }
    if ($wizard_secondary_used) {
      $form['#attributes']['class'][] = 'wizard-secondary-container';
    }

    $form['additional_settings']['#weight'] = 1000;
    $form['wizard_advanced']['additional_settings'] = $form['additional_settings'];
    unset($form['additional_settings']);

    // toggle display state for wizard_advanced vertical tabs
    $form['toggle_wizard_advanced'] = array(
      '#type' => 'checkbox',
      '#weight' => 1001,
      '#title' => t('Show advanced settings'),
    );
    $form['wizard_advanced']['#states'] = array(
      'invisible' => array("#edit-toggle-wizard-advanced" => array('checked' => FALSE)),
    );
    $form['field_reference_to_campaign']['#weight'] = 1;

    return $form;
  }

  public function validateStep($form, &$form_state) {
    node_form_validate($form, $form_state);
  }

  public function submitStep($form, &$form_state) {
    $submit_handlers = $form['#submit']; unset($form['#submit']);
    node_form_submit($form, $form_state);
    $form_state['form_info']['path'] = 'node/' . $form_state['node']->nid . '/wizard/%step';
    $form['#submit'] = $submit_handlers; unset($submit_handlers);
  }
}

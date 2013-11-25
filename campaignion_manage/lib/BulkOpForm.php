<?php

namespace Drupal\campaignion_manage;

class BulkOpForm {
  protected $listing;
  protected $ops;
  public function __construct($listing, $ops = array()) {
    $this->listing = $listing;
    $this->ops = array();
    foreach ($ops as $op) {
      $this->ops[$op->machineName()] = $op;
    }
  }
  public function form($form, &$form_state) {
    $form['#tree'] = TRUE;
    $form['operations'] = array(
      '#type' => 'radios',
      '#title' => t('Selected bulk operation'),
      '#options' => array(),
    );

    foreach ($this->ops as $name => $op) {
      $form['operations']['#options'][$name] = $op->title();
      $form['op'][$name] = array(
        '#type' => 'fieldset',
        '#title' => $op->title(),
      );
      $element = &$form['op'][$name];
      $op->formElement($element, $form_state);
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Apply'),
    );

    $form['listing'] = array();

    $this->listing->build($form['listing'], $form_state);
    return $form;
  }
  public function submit(&$form, &$form_state) {
    $values = &$form_state['values'];
    $nids = $this->listing->selectedNids($form['listing'], $form_state);

    $op_name = $values['operations'];
    if (!isset($this->ops[$op_name])) {
      return;
    }
    $op = $this->ops[$op_name];
    $op->apply($nids);
  }
}

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
    $form['bulk-wrapper'] = array(
      '#type' => 'fieldset',
      '#title' => t('Bulk edit'),
    );
    $form['bulk-wrapper']['operations'] = array(
      '#type' => 'radios',
      '#title' => t('Selected bulk operation'),
      '#options' => array(),
    );
    $form['bulk-wrapper']['op-wrapper'] = array(
      '#type' => 'container',
    );

    foreach ($this->ops as $name => $op) {
      $form['bulk-wrapper']['operations']['#options'][$name] = $op->title();
      $form['bulk-wrapper']['op-wrapper']['op'][$name] = array(
        '#type' => 'fieldset',
        '#title' => $op->title(),
      );
      $element = &$form['bulk-wrapper']['op-wrapper']['op'][$name];
      $op->formElement($element, $form_state);

      $name .= "-2";
      $form['bulk-wrapper']['operations']['#options'][$name] = $op->title();
      $form['bulk-wrapper']['op-wrapper']['op'][$name] = array(
        '#type' => 'fieldset',
        '#title' => $op->title(),
      );
      $element = &$form['bulk-wrapper']['op-wrapper']['op'][$name];
      $op->formElement($element, $form_state);
    }

    $form['bulk-wrapper']['submit'] = array(
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

    $op_name = $values['bulk-wrapper']['operations'];
    if (!isset($this->ops[$op_name])) {
      return;
    }
    $op = $this->ops[$op_name];
    $op->apply($nids);
  }
}

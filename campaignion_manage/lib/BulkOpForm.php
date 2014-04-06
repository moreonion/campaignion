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
      '#attributes' => array('class' => array('bulkops')),
      '#title' => t('Bulk edit'),
    );
    $form['bulk-wrapper']['operations'] = array(
      '#type' => 'radios',
      '#title' => t('Selected bulk operation'),
      '#options' => array(),
      '#attributes' => array('class' => array('bulkops-radios')),
    );
    $form['bulk-wrapper']['op-wrapper'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('bulkops-ops')),
    );

    foreach ($this->ops as $name => $op) {
      $form['bulk-wrapper']['operations']['#options'][$name] = $op->title();
      $form['bulk-wrapper']['op-wrapper']['op'][$name] = array(
        '#type' => 'fieldset',
        '#title' => $op->title(),
        '#attributes' => array('class' => array('bulkops-op', 'bulkops-op-' . $name)),
      );
      $element = &$form['bulk-wrapper']['op-wrapper']['op'][$name];
      $element['helptext'] = array(
        '#type' => 'container',
        '#attributes' => array('class' => array('help-text')),
        'text' => array('#markup' => $op->helpText()),
      );
      $op->formElement($element, $form_state);
    }

    $form['bulk-wrapper']['actions'] = array(
      '#type' => 'container',
    );
    $form['bulk-wrapper']['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Apply'),
    );

    $form['listing'] = array();
    $this->listing->build($form['listing'], $form_state);

    $form['#attributes']['class'][] = 'campaignion-manage-bulkops';
    return $form;
  }
  public function submit(&$form, &$form_state) {
    $values = &$form_state['values'];
    $nids = $this->listing->selectedIds($form['listing'], $form_state);

    $op_name = $values['bulk-wrapper']['operations'];
    if (!isset($this->ops[$op_name])) {
      return;
    }
    $op = $this->ops[$op_name];
    $op->apply($nids);
  }
}

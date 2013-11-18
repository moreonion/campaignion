<?php

namespace Drupal\campaignion_manage;

class FilterForm {
  protected $query;
  protected $filters;
  protected $values;
  public function __construct($query, $filters = array()) {
    $this->query = $query;
    $this->filters = $filters;
  }
  public function applyFilters() {
    foreach ($this->filters as $filter) {
      $name = $filter->machineName();
      if ($this->filterIsActive($filter->machineName())) {
        $filter->apply($this->query, $this->values['filter'][$name]);
      }
    }
  }
  public function form($form, &$form_state) {
    $form_state['filterForm'] = $this;
    $form['#tree'] = TRUE;

    foreach ($this->filters as $filter) {
      $name = $filter->machineName();
      $form['filter'][$name] = array(
        '#type' => 'fieldset',
        '#title' => $filter->title(),
      );
      $element = &$form['filter'][$name];
      $element['active'] = array(
        '#type' => 'checkbox',
        '#title' => t('active'),
        '#description' => t('The filter will only be applied if this checkbox is checked.'),
      );
      $filter->form($element, $form_state,  $this->values['filter'][$name]);
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Filter'),
    );
    return $form;
  }

  public function submit(&$form, &$form_state) {
    $this->values = $form_state['values'];
    foreach ($this->filters as $filter) {
      $name = $filter->machineName();
      if (!isset($this->values['filter'][$name])) {
        $this->values['filter'][$name] = array();
      }
    }
    $this->applyFilters();
  }

  protected function filterIsActive($name) {
    $f = &$this->values['filter'];
    return isset($f[$name]) && isset($f[$name]['active']) && $f[$name]['active'];
  }
}

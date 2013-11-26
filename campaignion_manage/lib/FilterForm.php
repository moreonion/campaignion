<?php

namespace Drupal\campaignion_manage;

class FilterForm {
  protected $filters;
  protected $values;
  public function __construct($filters = array()) {
    $this->filters = $filters;
    $this->values = isset($_SESSION['campaignion_manage_content_filter']) ? $_SESSION['campaignion_manage_content_filter'] : NULL;
    foreach ($this->filters as $filter) {
      $name = $filter->machineName();
      if (!isset($this->values['filter'][$name])) {
        $this->values['filter'][$name] = array();
      }
    }
  }

  public function applyFilters($query) {
    foreach ($this->filters as $filter) {
      $name = $filter->machineName();
      if ($this->filterIsActive($filter->machineName())) {
        $filter->apply($query, $this->values['filter'][$name]);
      }
    }
  }

  public function form($form, &$form_state) {
    $form['#tree'] = TRUE;

    foreach ($this->filters as $filter) {
      $name = $filter->machineName();
      $form['filter'][$name] = array(
        '#type' => 'fieldset',
        '#title' => $filter->title(),
        '#attributes' => array('class' => array('clearfix')),
      );
      $element = &$form['filter'][$name];
      $element['active'] = array(
        '#type' => 'checkbox',
        '#title' => t('active'),
        '#description' => t('The filter will only be applied if this checkbox is checked.'),
        '#default_value' => isset($this->values['filter'][$name]) && isset($this->values['filter'][$name]['active']),
        '#attributes' => array('class' => array('filter-active-toggle')),
      );
      $filter->formElement($element, $form_state,  $this->values['filter'][$name]);
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Filter'),
    );
    return $form;
  }

  public function submit(&$form, &$form_state) {
    $form_state['redirect'] = FALSE;
    $this->values = $form_state['values'];
    $_SESSION['campaignion_manage_content_filter'] = $this->values;
  }

  protected function filterIsActive($name) {
    $f = &$this->values['filter'];
    return isset($f[$name]) && isset($f[$name]['active']) && $f[$name]['active'];
  }
}

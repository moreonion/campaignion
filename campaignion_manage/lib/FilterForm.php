<?php

namespace Drupal\campaignion_manage;

class FilterForm {
  protected $filters;
  protected $values;

  /**
   * @param $filters array of filters with the structure
   *   $filters = array(
   *     machineName => filterObject,
   *   )
   *   machineName      is the machine name of the filter
   *   filterObject     instance of a filter object
   */
  public function __construct($filters = array(), $defaultActive = array()) {
    $this->values = isset($_SESSION['campaignion_manage_content_filter']) ? $_SESSION['campaignion_manage_content_filter'] : NULL;

    $this->filters = $filters;

    foreach ($filters as $name => $filter) {
      for ($delta = 0; $delta < $filter->nrOfInstances(); $delta++) {
        if (!isset($this->values['filter'][$name][$delta]['active'])) {
          if (($index = array_search($name, $defaultActive)) !== FALSE) {
            $this->values['filter'][$name][$delta]['active'] = TRUE;
            // only set the first instance of the filter active
            unset($defaultActive[$index]);
          }
          else {
            $this->values['filter'][$name][$delta]['active'] = FALSE;
          }
        }
      }
    }
  }

  public function applyFilters($query) {
    foreach ($this->filters as $filter) {
      $name = $filter->machineName();
      for($delta = 0; $delta < $filter->nrOfInstances(); $delta++) {
        if ($this->filterIsActive($name, $delta)) {
          $filter->apply($query, $this->values['filter'][$name][$delta]);
        }
      }
    }
  }

  public function form($form, &$form_state) {
    $form['#tree'] = TRUE;
    foreach ($this->filters as $filter) {
      $name = $filter->machineName();
      for($delta = 0; $delta < $filter->nrOfInstances(); $delta++) {
        $form['filter'][$name][$delta] = array(
          '#type'       => 'fieldset',
          '#title'      => $filter->title(),
          '#attributes' => array('class' => array('clearfix')),
        );
        $element = &$form['filter'][$name][$delta];
        $element['active'] = array(
          '#type'          => 'checkbox',
          '#title'         => t('active'),
          '#description'   => t('The filter will only be applied if this checkbox is checked.'),
          '#default_value' => !empty($this->values['filter'][$name][$delta]['active']),
          '#attributes'    => array('class' => array('filter-active-toggle')),
        );

        $filter->formElement($element, $form_state, $this->values['filter'][$name][$delta]);
      }
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

  protected function filterIsActive($name, $delta) {
    $f = &$this->values['filter'][$name][$delta];
    return isset($f) && $f['active'];
  }
}

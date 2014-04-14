<?php

namespace Drupal\campaignion_manage;

abstract class Page {
  protected $baseQuery;
  public $filterForm;
  protected $bulkOpForm;
  protected $listing;
  public function renderable() {
    $output = drupal_get_form('campaignion_manage_form', $this);
    return $output;
  }

  public function form($form, &$form_state) {
    $this->baseQuery->__sleep();
    $form['#tree'] = TRUE;

    $form['filter'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('manage-filter-form')),
    );
    $filterValues = $form_state['submitted'] ? $form_state['values']['filter'] : NULL;
    $this->filterForm->form($form['filter'], $form_state, $filterValues);

    $form['bulkop'] = array(
      '#type' => 'container',
    );
    $this->bulkOpForm->form($form['bulkop'], $form_state);

    $form['listing'] = array();
    $this->listing->build($form['listing'], $form_state);

    $form['pager']['#theme'] = 'pager';
    $form['#theme_wrappers'][] = 'form';
    $form['#theme_wrappers'][] = 'campaignion_manage_ajax';
    $form['#attached']['js'] = array(
      drupal_get_path('module', 'campaignion_manage') . '/js/manage_bulk.js',
      drupal_get_path('module', 'campaignion_manage') . '/js/manage_filter.js',
    );
    $form['#attached']['css'] = array(
      drupal_get_path('module', 'campaignion_manage') . '/css/manage_bulk.css',
    );
    $form['#process'] = array('campaignion_manage_form_process');
    return $form;
  }

  public function process(&$form, &$form_state) {
    $this->baseQuery->reset();
    $this->filterForm->applyFilters($this->baseQuery->filtered());
    $this->filterForm->process($form['filter'], $form_state);
    $this->listing->process($form['listing'], $form_state, $this->baseQuery);
  }

  public function submit($form, &$form_state) {
    switch ($form_state['clicked_button']['#parents'][0]) {
      case 'filter':
        $this->filterForm->submit($form['filter'], $form_state);
        break;
      case 'bulkop':
        $ids = $this->listing->selectedIds($form['listing'], $form_state);
        $this->bulkOpForm->submit($form['bulkops_listing'], $form_state, $ids);
        break;
    }
  }
}

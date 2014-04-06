<?php

namespace Drupal\campaignion_manage;

abstract class Page {
  protected $baseQuery;
  public $filterForm;
  protected $pageSize = 20;
  protected $bulkOpForm;
  public function renderable() {
    // add style + js for interface
    drupal_add_js(drupal_get_path('module', 'campaignion_manage') . '/js/manage_bulk.js');
    drupal_add_js(drupal_get_path('module', 'campaignion_manage') . '/js/manage_filter.js');
    drupal_add_css(drupal_get_path('module', 'campaignion_manage') . '/css/manage_bulk.css');

    $baseQuery = $this->baseQuery;
    $baseQuery->setFilter($this->filterForm);
    $baseQuery->page($this->pageSize);

    $output = array(
      'filter' => drupal_get_form('campaignion_manage_filter_form', $this->filterForm),
      'bulkop_listing' => drupal_get_form('campaignion_manage_bulkops_form', $this->bulkOpForm),
      'pager' => array(
        '#theme' => 'pager',
      ),
      '#theme_wrappers' => array('campaignion_manage_ajax'),
    );
    return $output;
  }
}

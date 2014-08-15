<?php

namespace Drupal\campaignion_manage;

class SupporterPage extends Page {
  public function __construct($query) {
    $this->baseQuery = $query;

    $filters['name'] = new Filter\SupporterName();
    if (module_exists('campaignion_supporter_tags')) {
      $filters['tags'] = new Filter\SupporterTag($this->baseQuery->query());
    }
    $filters['country']  = new Filter\SupporterCountry($this->baseQuery->query());
    $filters['activity'] = new Filter\SupporterActivity($this->baseQuery->query());
    $default[] = array('type' => 'name', 'removable' => FALSE);
    $this->filterForm = new FilterForm('supporter', $filters, $default);

    $bulkOps = array();
    if (module_exists('campaignion_supporter_tags')) {
      $bulkOps['tag']   = new BulkOp\SupporterTag(TRUE);
      $bulkOps['untag'] = new BulkOp\SupporterTag(FALSE);
    }
    $bulkOps['export'] = new BulkOp\SupporterExport();
    $this->listing = new SupporterListing(20);
    $this->bulkOpForm = new BulkOpForm($bulkOps);
  }

  public function form($form, &$form_state) {
    $form = parent::form($form, $form_state);
    array_push($form['filter'], array(
        '#type' => 'fieldset',
        '#weight' => 3,
        'live_update' => array(
          '#type' => 'checkbox',
          '#title' => t('Update the listing right away'),
          '#attributes' => array(
            'class' => array('no-itoggle'),
          ),
        )));
    return $form;

  }
}

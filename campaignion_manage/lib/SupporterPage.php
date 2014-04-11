<?php

namespace Drupal\campaignion_manage;

class SupporterPage extends Page {
  public function __construct($query) {
    $this->baseQuery = $query;

    $filters['name'] = new Filter\SupporterName();
    if (module_exists('campaignion_supporter_tags')) {
      $filters['tags'] = new Filter\SupporterTag($this->baseQuery->getQuery());
    }
    $filters['country'] = new Filter\SupporterCountry($this->baseQuery->getQuery());
    $this->filterForm = new FilterForm($filters, array('name'));

    $bulkOps = array();
    if (module_exists('campaignion_supporter_tags')) {
      $bulkOps['tag']   = new BulkOp\SupporterTag(TRUE);
      $bulkOps['untag'] = new BulkOp\SupporterTag(FALSE);
    }
    $bulkOps['export'] = new BulkOp\SupporterExport();
    $listing = new SupporterListing($this->baseQuery, 20);
    $this->bulkOpForm = new BulkOpForm($listing, $bulkOps);
  }
}

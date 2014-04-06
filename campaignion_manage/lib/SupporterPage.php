<?php

namespace Drupal\campaignion_manage;

class SupporterPage extends Page {
  public function __construct($query) {
    $this->baseQuery = $query;

    if (module_exists('campaignion_supporter_tags')) {
      $filters['tags'] = new Filter\SupporterTag($this->baseQuery->getQuery());
    }
    $filters['name'] = new Filter\SupporterName();
    $this->filterForm = new FilterForm($filters, array('name'));

    $listing = new SupporterListing($this->baseQuery);
    $this->bulkOpForm = new BulkOpForm($listing, array());
  }
}

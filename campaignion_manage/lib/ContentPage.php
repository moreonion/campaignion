<?php

namespace Drupal\campaignion_manage;

class ContentPage extends Page {
  public function __construct($query) {
    $this->baseQuery = $query;
    $select = $query->getQuery();

    $filters['title'] = new Filter\ContentTitle();
    $defaultActive = array('title');
    if (module_exists('campaignion_microsite')) {
      $filters['microsite'] = new Filter\ContentMicrosite($select);
    }
    if (module_exists('campaignion_campaign')) {
      $filters['campaign'] = new Filter\ContentCampaign($select);
    }
    $filters['language'] = new Filter\ContentLanguage($select);
    $filters['type'] = new Filter\ContentType($select);
    $filters['status'] = new Filter\ContentStatus();
    $this->filterForm = new FilterForm($filters, array('title'));

    $listing = new ContentListing($this->baseQuery, 20);
    $this->bulkOpForm = new BulkOpForm($listing, array(
      'publish' => new BulkOp\ContentPublish(),
      'unpubslih' => new BulkOp\ContentUnpublish(),
    ));
  }
}

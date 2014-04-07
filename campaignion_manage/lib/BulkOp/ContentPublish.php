<?php

namespace Drupal\campaignion_manage\BulkOp;

class ContentPublish {
  public function __construct() {
  }
  public function title() { return t('Publish'); }
  public function helpText() {
    return t('Publishing your content will make it visible to the users of your site. Usually this includes all visitors of your site, but this depends on your permission settings.');
  }
  public function formElement(&$element, &$form_state) {
  }
  public function apply($nids, &$form_state) {
    $nodes = node_load_multiple($nids);
    foreach ($nodes as $node) {
      if (!$node->status) {
        node_publish_action($node);
        node_save($node);
      }
    }
  }
}

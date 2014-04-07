<?php

namespace Drupal\campaignion_manage\BulkOp;

class ContentUnpublish {
  public function __construct() {
  }
  public function title() { return t('Unpublish'); }
  public function helpText() {
    return t('Unpublishing your content will make it invisible to most users of your site. Only users with a special permission are allowed to see unpublished content.');
  }
  public function formElement(&$element, &$form_state) {
  }
  public function apply($nids, &$form_state) {
    $nodes = node_load_multiple($nids);
    foreach ($nodes as $node) {
      if ($node->status) {
        node_unpublish_action($node);
        node_save($node);
      }
    }
  }
}

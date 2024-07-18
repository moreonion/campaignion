<?php

namespace Drupal\campaignion_manage_clone;

/**
 * Bulk operation to clone actions.
 */
class ContentClone {

  public function __construct() {
  }

  public function title() {
    return t('Clone');
  }

  public function helpText() {
    return t('Clone an page or action including the form, all emails and thank you pages.');
  }

  public function formElement(&$element, &$form_state) {
  }

  public function apply($nids, $values) {
    module_load_include('pages.inc', 'clone');
    $messages = [];
    $nodes = node_load_multiple($nids);
    foreach ($nodes as $node) {
      clone_node_save($node->nid);
    }
    return $messages;
  }

  /**
   * Check if the currently active user has access to the operation.
   */
  public function userHasAccess() {
    return user_access('bypass node access') || user_access('administer nodes');
  }

}

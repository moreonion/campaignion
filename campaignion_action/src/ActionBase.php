<?php

namespace Drupal\campaignion_action;

class ActionBase {
  protected $type;
  protected $node;

  public static function fromTypeAndNode(TypeInterface $type, $node) {
    return new static($type, $node);
  }

  public function __construct(TypeInterface $type, $node) {
    $this->type = $type;
    $this->node = $node;
  }

  /**
   * Called whenever hook_node_presave() is called on this node.
   */
  public function presave() {
    $node = $this->node;
    if (isset($node->translation_source)) {
      // webform_template-7.x-1.x
      $_SESSION['webform_template'] = $node->translation_source->nid;
      // webform_template-7.x-4.x
      $node->webform_template = $node->translation_source->nid;
    } else {
      if (!isset($node->nid) && empty($node->webform['components'])) {
        if ($nid = $this->type->defaultTemplateNid()) {
          // webform_template-7.x-1.x
          $_SESSION['webform_template'] = $nid;
          // webform_template-7.x-4.x
          $node->webform_template = (int) $nid;
        }
      }
    }
  }

  /**
   * Called whenever hook_node_prepare is called on this node.
   */
  public function prepare() {
    if (!isset($this->node->webform['confirm_email_request_lifetime'])) {
      $s90days = 90 * 24 * 3600;
      $this->node->webform['confirm_email_request_lifetime'] = $s90days;
    }
  }

  /**
   * Called whenever the node is saved (either by update or insert).
   */
  public function save() {
  }

  /**
   * Called whenever hook_node_update() is called on this node.
   */
  public function update() {
    $this->save();
  }

  /**
   * Called whenever hook_node_insert() is called on this node.
   */
  public function insert() {
    $this->save();
  }

  /**
   * Generate a test-link for this action.
   *
   * @return \Drupal\campaignion_action\SignedLink
   *   A test link or NULL if there should be none for this action-type.
   */
  public function testLink($title, $query = [], $options = []) {
    return NULL;
  }

  protected function _testLink($title, $query = [], $options = []) {
    $query['test-mode'] = 1;
    $options['attributes']['class'][] = 'test-mode-link';
    $options += ['html' => FALSE];
    $l = new SignedLink("node/{$this->node->nid}", $query);
    return [
      '#theme' => 'link',
      '#text' => $title,
      '#path' => $l->path,
      '#options' => ['query' => $l->hashedQuery()] + $options,
    ];
  }

}

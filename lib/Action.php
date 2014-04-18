<?php

namespace Drupal\campaignion;

class Action {
  protected $type;
  protected $node;

  public static function fromNode($node) {
    if (isset($node->action)) {
      return $node->action;
    }
    $type = Action\TypeBase::fromContentType($node->type);
    // give type the control over which class is used.
    return $type->actionFromNode($node);
  }

  public function __construct(Action\TypeInterface $type, $node) {
    $this->type = $type;
    $this->node = $node;
    $this->node->action = $this;
  }

  public function applyDefaultTemplate() {
    // Only apply defaults if no components are specified.
    if (!empty($this->node->webform['components'])) {
      return;
    }
    if ($nid = $this->type->defaultTemplateNid()) {
      $this->copyForm($nid);
    }
  }

  public function copyForm($nid) {
    $_SESSION['webform_template'] = $nid;
    webform_template_node_insert($this->node);
    unset($_SESSION['webform_template']);
  }
}

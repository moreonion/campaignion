<?php

namespace Drupal\campaignion;

class Action {
  protected $type;
  protected $node;

  public static function fromNode($node) {
    if (isset($node->action)) {
      return $node->action;
    }
    $type = ActionType::fromContentType($node->type);
    // give type the control over which class is used.
    return $type->actionFromNode($node);
  }

  public function __construct(ActionType $type, $node) {
    $this->type = $type;
    $this->node = $node;
    $this->node->action = $this;
  }

  public function applyDefaultTemplate() {
    if ($nid = $this->type->defaultTemplateNid()) {
      $_SESSION['webform_template'] = $this->type->defaultTemplateNid();
      _webform_template_attach($this->node, 'update');
      unset($_SESSION['webform_template']);
    }
  }
}

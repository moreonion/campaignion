<?php

namespace Drupal\campaignion;

class Action {
  protected $type;
  protected $node;

  public static function fromNode($node) {
    if (isset($node->action)) {
      return $node->action;
    }
    if ($type = Action\TypeBase::fromContentType($node->type)) {
      // give type the control over which class is used.
      return $type->actionFromNode($node);
    }
  }

  public function __construct(Action\TypeInterface $type, $node) {
    $this->type = $type;
    $this->node = $node;
    $this->node->action = $this;
  }

  public function presave() {
    $node = $this->node;
    if (isset($node->translation_source)) {
      $_SESSION['webform_template'] = $node->translation_source->nid;
    } else {
      if (!isset($node->nid) && empty($node->webform['components'])) {
        if ($nid = $this->type->defaultTemplateNid()) {
          $_SESSION['webform_template'] = $nid;
        }
      }
    }
  }

  public function prepare() {
    $node = $this->node;
    if (module_exists('webform_ajax') && isset($node->webform)) {
      $node->webform += array(
        'webform_ajax' => 1,
      );
    }
  }
}

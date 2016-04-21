<?php

namespace Drupal\campaignion_action;

abstract class TypeBase implements TypeInterface {
  /**
   * Content-type
   */
  protected $type;
  /**
   * Parameters
   */
  protected $parameters;

  public function __construct($type, array $parameters = array()) {
    $this->type = $type;
    $this->parameters = $parameters;
  }

  public function defaultTemplateNid() {
    return NULL;
  }

  public function actionFromNode($node) {
    return new ActionBase($this, $node);
  }

  /**
   * {@inheritdoc}
   */
  public function isDonation() {
    return FALSE;
  }
}

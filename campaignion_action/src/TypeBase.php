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
    $this->parameters = $parameters + [
      'action_class' => '\\Drupal\\campaignion_action\\ActionBase',
    ];
  }

  public function defaultTemplateNid() {
    $uuid = $this->parameters['template_node_uuid'];
    $ids = \entity_get_id_by_uuid('node', [$uuid]);
    return array_shift($ids);
  }

  public function wizard($node = NULL) {
    $class = $this->parameters['wizard_class'];
    return new $class($this->parameters, $node, $this->type);
  }

  public function actionFromNode($node) {
    $class = $this->parameters['action_class'];
    return new $class($this, $node);
  }

  /**
   * {@inheritdoc}
   */
  public function isDonation() {
    return FALSE;
  }

}

<?php

namespace Drupal\campaignion_action;

class TypeBase implements TypeInterface {

  /**
   * Content-type
   */
  protected $type;
  /**
   * Parameters
   */
  public $parameters;

  public function __construct($type, array $parameters = array()) {
    $this->type = $type;
    $this->parameters = $parameters + [
      'action_class' => '\\Drupal\\campaignion_action\\ActionBase',
      'donation' => FALSE,
    ];
  }

  public function defaultTemplateNid() {
    $uuid = $this->parameters['template_node_uuid'];
    $ids = \entity_get_id_by_uuid('node', [$uuid]);
    return array_shift($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function isDonation() {
    return $this->parameters['donation'];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmailProtest() {
    return !empty($this->parameters['email_protest']);
  }

}

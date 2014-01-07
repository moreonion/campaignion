<?php

namespace Drupal\campaignion\Action;

abstract class TypeBase implements TypeInterface {
  /**
   * Content-type
   */
  protected $type;

  public function __construct($type) {
    $this->type = $type;
  }

  public function defaultTemplateNid() {
    return NULL;
  }

  public function actionFromNode($node) {
    return new \Drupal\campaignion\Action($this, $node);
  }

  public static function isAction($type) {
    $action_types = self::types();
    return isset($action_types[$type]);
  }

  public static function types() {
    static $static_fast = NULL;
    if (!isset($static_fast)) {
      $static_fast = &drupal_static(__CLASS__, array());
      $static_fast = \module_invoke_all('campaignion_action_info');
    }
    return $static_fast;
  }

  public static function fromContentType($type) {
    $action_types = self::types();
    if (isset($action_types[$type])) {
      $class = $action_types[$type];
      return new $class($type);
    } else {
      throw new \Exception('Trying to get ActionType for unregistered bundle.');
    }
  }
}

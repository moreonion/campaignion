<?php

namespace Drupal\campaignion;

class ActionType {
  public function defaultTemplateNid() {
    return NULL;
  }

  public function actionFromNode($node) {
    return new Action($this, $node);
  }

  public static function isAction($type) {
    $action_types = \module_invoke_all('action_type_info');
    return isset($action_types[$type]);
  }

  public static function fromContentType($type) {
    $action_types = \module_invoke_all('action_type_info');
    if (isset($action_types[$type])) {
      $class = $action_types[$type];
      return new $class();
    } else {
      throw new \Exception('Trying to get ActionType for unregistered bundle.');
    }
  }
}

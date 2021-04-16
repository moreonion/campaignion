<?php

namespace Drupal\campaignion_action;

use Drupal\little_helpers\Services\Container;

/**
 * Class for loading Action-Type plugins and general dependency injection.
 */
class Loader {

  /**
   * Action information keyed by node-type machine name.
   *
   * @var array
   */
  protected $info = [];

  /**
   * A action plugin loader.
   *
   * @var \Drupal\little_helpers\Services\Container
   */
  protected $actionLoader;

  /**
   * A wizard plugin loader.
   *
   * @var \Drupal\little_helpers\Services\Container
   */
  protected $wizardLoader;

  /**
   * Get a singleton instance of this class.
   */
  public static function instance() {
    return Container::get()->loadService('campaignion_action.loader');
  }

  /**
   * Create a new instance.
   *
   * @param \Drupal\little_helpers\Services\Container $action_loader
   *   A action plugin loader.
   * @param \Drupal\little_helpers\Services\Container $wizard_loader
   *   A wizard plugin loader.
   */
  public function __construct(Container $action_loader, Container $wizard_loader) {
    $this->actionLoader = $action_loader;
    $this->wizardLoader = $wizard_loader;
  }

  /**
   * Read node type definitions from a hook.
   *
   * @param string $hook
   *   The name of the hook that should be invoked. The corresponding alter-hook
   *   is invoked as well.
   */
  public function loadTypesFromHook(string $hook) : void {
    $types_info = module_invoke_all($hook);
    foreach ($types_info as &$info) {
      $info += [
        'type' => 'default',
        'wizard' => 'default',
      ];
      if (($p = $info['parameters'] ?? FALSE) && is_array($p)) {
        unset($info['parameters']);
        $info += $p;
      }
    }
    drupal_alter($hook, $types_info);
    $this->info += $types_info;
  }

  /**
   * Get all action node types and their info.
   *
   * @return array
   *   Array of action definitions keyed by node typemachine-name.
   */
  public function allTypes() {
    return $this->info;
  }

  /**
   * Get all node-types that are actions.
   *
   * @return array
   *   Array of all node-types that are also action-types.
   */
  public function actionNodeTypes() {
    return array_keys($this->info);
  }

  /**
   * Check if a node-type is an action-type.
   *
   * @param string $type
   *   Machine name of the node-type.
   *
   * @return boolean
   *   TRUE if the node-type $type is an action-type.
   */
  public function isActionType($type) {
    return isset($this->info[$type]);
  }

  /**
   * Get info for a node-type.
   *
   * @param string $type
   *   Machine name of the node-type.
   *
   * @return array
   *   The info-array for this node-type or FALSE if it is not defined.
   */
  public function type($type) {
    if (!isset($this->types[$type])) {
      $this->types[$type] = FALSE;
      if ($info = $this->info[$type] ?? NULL) {
        $this->types[$type] = new ActionType($type, $info + $info['parameters']);
      }
    }
    return $this->types[$type];
  }

  /**
   * Get action instance by node-type.
   */
  public function actionFromNode($node) {
    if (!isset($node->action)) {
      $node->action = NULL;
      if ($info = $this->info[$node->type] ?? NULL) {
        $spec = $this->actionLoader->getSpec($info['type']);
        $node->action = $spec->instantiate([
          'node' => $node,
          'parameters' => $info,
        ]);
      }
    }
    return $node->action;
  }

  /**
   * Return a wizard object for a node-type.
   *
   * @param string $type
   *   The node-type.
   * @param object|null $node
   *   The node to edit. Create a new one if NULL.
   *
   * @return \Drupal\oowizard\Wizard
   *  The wizard responsible for changing/adding actions of this type.
   */
  public function wizard($type, $node = NULL) {
    if ($info = $this->info[$type] ?? NULL) {
      $spec = $this->wizardLoader->getSpec($info['wizard']);
      return $spec->instantiate([
        'type' => $type,
        'node' => $node,
        'parameters' => $info,
      ]);
    }
  }

  /**
   * Get all node-types that are referenced as thank-you pages.
   */
  protected function thankYouPageTypes() {
    $tyTypes = [];
    foreach ($this->info as $type => $p) {
      if (isset($p['thank_you_page'])) {
        $tyTypes[$p['thank_you_page']['type']][$p['thank_you_page']['reference']] = TRUE;
      }
    }
    return $tyTypes;
  }

  /**
   * Get names of all fields referencing a thank-you page type.
   *
   * @param string $type
   *   Node-type of the thank-you page.
   * @return array
   *   Array of field names that may reference nodes of this type.
   */
  protected function referenceFieldsByType($type) {
    $types = $this->thankYouPageTypes();
    if (isset($types[$type])) {
      return array_keys($types[$type]);
    }
    return [];
  }

  /**
   * Get an action-node's nid by one of it's thank-you page nodes.
   */
  public function actionNidByThankYouNode($node) {
    foreach ($this->referenceFieldsByType($node->type) as $field) {
      // Lookup the action that uses this thank you page.
      $sql = "SELECT entity_id FROM {field_data_$field} WHERE entity_type='node' AND {$field}_node_reference_nid=:nid LIMIT 1";
      $result = db_query($sql, array(':nid' => $node->nid));
      if ($nid = $result->fetchField()) {
        return $nid;
      }
    }
  }

}

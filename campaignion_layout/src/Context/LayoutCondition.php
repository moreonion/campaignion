<?php

namespace Drupal\campaignion_layout\Context;

use Drupal\little_helpers\Services\Container;
use Drupal\campaignion_layout\Lookup;

/**
 * Expose available layouts as a context condition.
 */
class LayoutCondition extends \context_condition {

  /**
   * Condition values.
   */
  public function condition_values() {
    $values = [];
    $themes = Container::get()->loadService('campaignion_layout.themes')->enabledThemes();
    foreach ($themes as $theme) {
      $values += $theme->layoutOptions(TRUE);
    }
    return $values;
  }

  /**
   * Check whether the condition is met.
   *
   * @param object $node
   *   The node object.
   * @param string $op
   *   The node-related operation: 'view', 'form', 'comment'.
   */
  public function execute($node, $op) {
    if ($op === 'view' && $layout = Lookup::fromEntity('node', $node)->getLayout()) {
      foreach ($this->get_contexts($layout['name']) as $context) {
        $this->condition_met($context, $layout['name']);
      }
    }
  }

}

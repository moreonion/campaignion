<?php

namespace Drupal\campaignion_layout\Context;

use Drupal\little_helpers\Services\Container;

/**
 * Expose available themes as a context condition.
 */
class ThemeCondition extends \context_condition {

  /**
   * Condition values.
   */
  public function condition_values() {
    $values = [];
    $themes = Container::get()->loadService('campaignion_layout.themes')->enabledThemes();
    foreach ($themes as $name => $theme) {
      $values[$name] = $theme->title();
    }
    return $values;
  }

  /**
   * Check whether the condition is met.
   */
  public function execute(string $theme) {
    foreach ($this->get_contexts($theme) as $context) {
      $this->condition_met($context, $theme);
    }
  }

}

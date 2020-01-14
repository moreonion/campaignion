<?php

namespace Drupal\campaignion_email_to_target;

use Drupal\campaignion_action\TypeBase;

use Drupal\campaignion_email_to_target\Channel\Email;

/**
 * Action type for email to target actions.
 */
class ActionType extends TypeBase {

  /**
   * Add more default values for the parent constructor.
   */
  public function __construct($type, array $parameters = array()) {
    $parameters += [
      'channel' => Email::class,
    ];
    parent::__construct($type, $parameters);
  }

  /**
   * Get the channel for this to-target action type.
   */
  public function getChannel() {
    return $this->pluginInstance($this->parameters['channel']);
  }

  /**
   * Create a new plugin instance based on a specification.
   *
   * @param mixed $spec
   *   A spec can either be a fully qualified class name or an array with at
   *   least one member 'class' which must be a fully qualified class name.
   *
   * @return mixed
   *   A new plugin instance.
   */
  protected function pluginInstance($spec) {
    if (!is_array($spec)) {
      $spec = ['class' => $spec];
    }
    $class = $spec['class'];
    return $class::fromConfig($spec);
  }

}

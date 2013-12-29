<?php

namespace Drupal\campaignion\Action;

/**
 * Every ActionType (Petition) has to implement this interface.
 */
interface TypeInterface {
  /**
   * Return a wizard object for this ActionType.
   *
   * @param object $node The node to edit. Create a new one if NULL.
   *
   * @return \Drupal\wizard\Wizard
   *  The wizard responsible for changing/adding actions of this type.
   */
  public function wizard($node = NULL);
}

<?php

namespace Drupal\campaignion_newsletters_optivo;

/**
 * A SoapClient that adds the session-id parameter to all it's calls.
 */
class SessionClient extends Client {
  protected $sessionId = NULL;

  /**
   * Set the session-id.
   */
  public function setSessionId($session_id) {
    $this->sessionId = $session_id;
  }

  /**
   * Override SoapClient::__call() to add the session-id.
   */
  public function __call($name, $arguments) {
    array_unshift($arguments, $this->sessionId);
    return parent::__call($name, $arguments);
  }
}

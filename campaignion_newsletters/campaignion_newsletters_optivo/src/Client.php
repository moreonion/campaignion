<?php

namespace Drupal\campaignion_newsletters_optivo;

use \Drupal\campaignion_newsletters\ApiError;

/**
 * A SoapClient + error-handling.
 */
class Client extends \SoapClient {
  /**
   * Override SoapClient::__call().
   *
   * All SoapFault exceptions are considered temporary APIErrors.
   */
  public function __call($name, $arguments) {
    try {
      return parent::__call($name, $arguments);
    }
    catch (\SoapFault $e) {
      throw new ApiError('Optivo', 'Exception during API-call', [], 0, NULL, $e);
    }
  }
}

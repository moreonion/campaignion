<?php

namespace Drupal\campaignion_email_to_target;

use Drupal\campaignion_action\ActionBase;
use Drupal\campaignion_action\ActionType;
use Drupal\campaignion_email_to_target\Action;
use Drupal\campaignion_email_to_target\Api\Client;
use Drupal\campaignion_email_to_target\Channel\EmailNoSend;
use Drupal\little_helpers\Services\Container;
use Drupal\little_helpers\Services\Spec;
use Drupal\little_helpers\Webform\Submission;

/**
 * Defines special behavior for email to target actions.
 *
 * Mainly deals with the configuration and with selecting messages / exclusion.
 */
class ActionNoTestMode extends Action {

  /**
   * Create a new action instance.
   *
   * @param array $parameters
   *   Additional action parameters.
   * @param object $node
   *   The action’s node.
   * @param \Drupal\campaignion_email_to_target\Api\Client $api
   *   Api client for the e2t_api serivce.
   */
  public function __construct(array $parameters, $node, Client $api = NULL) {
    parent::__construct($parameters + [
      'channel' => EmailNoSend::class,
    ], $node);
    $this->options = $this->getOptions();
    $this->api = $api ?? Container::get()->loadService('campaignion_email_to_target.api.Client');
  }

  /**
   * Create a link to view the action in test-mode.
   */
  public function testLink($title, $query = [], $options = []) {
      return NULL;
  }
}

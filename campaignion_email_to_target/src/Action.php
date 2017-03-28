<?php

namespace Drupal\campaignion_email_to_target;

use \Drupal\campaignion_action\ActionBase;
use \Drupal\campaignion_action\TypeInterface;
use \Drupal\campaignion_email_to_target\Api\Client;

/**
 * Defines special behavior for email to target actions.
 *
 * Mainly deals with the configuration and with selecting messages / exclusion.
 */
class Action extends ActionBase {

  protected $options;
  protected $api;

  public static function fromTypeAndNode(TypeInterface $type, $node) {
    return new static($type, $node, Client::fromConfig());
  }

  public function __construct(TypeInterface $type, $node, $api) {
    parent::__construct($type, $node);
    $this->options = $this->getOptions();
    $this->api = $api;
  }

  /**
   * Choose an appropritae exclusion for a given target.
   */
  public function getExclusion($constituency) {
    foreach (MessageTemplate::byNid($this->node->nid) as $t) {
      if ($t->type == 'exclusion' && $t->checkFilters([], $constituency)) {
        return Message::fromTemplate($t);
      }
    }
  }

  /**
   * Choose an appropriate message for a given target.
   */
  public function getMessage($target, $constituency) {
    $templates = MessageTemplate::byNid($this->node->nid);
    foreach ($templates as $t) {
      if ($t->checkFilters($target, $constituency)) {
        return Message::fromTemplate($t);
      }
    }
    watchdog('campaignion_email_to_target', 'No message found for target');
    return NULL;
  }

  /**
   * Get options for this action.
   */
  public function getOptions() {
    $field = $this->type->parameters['email_to_target']['options_field'];
    $items = field_get_items('node', $this->node, $field);
    return $items ? $items[0] : ['dataset_name' => 'mp'];
  }

  /**
   * Get configured no target message.
   */
  public function noTargetMessage() {
    $field = $this->type->parameters['email_to_target']['no_target_message_field'];
    return field_view_field('node', $this->node, $field, ['label' => 'hidden']);
  }

  /**
   * Get the selected dataset for this action.
   */
  public function dataset() {
    return $this->api->getDataset($this->getOptions()['dataset_name']);
  }

  /**
   * Create a link to view the action in test-mode.
   */
  public function testLink($title, $query = [], $options = []) {
    return $this->_testLink($title, $query, $options);
  }

  /**
   * Generate target message pairs for a submission.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission_o
   *   A submisson object.
   * @param bool $test_mode
   *   Whether to replace all target email addresses.
   *
   * @return array
   *   Array with two members:
   *   1. An array of target / message pairs.
   *   2. The element that should be rendered if no target was found.
   */
  public function targetMessagePairs($submission_o, $test_mode = FALSE) {
    $dataset = $this->options['dataset_name'];
    $email_override = $test_mode ? $submission_o->valueByKey('email') : NULL;
    $postcode = str_replace(' ', '', $submission_o->valueByKey('postcode'));
    $constituencies = $this->api->getTargets($dataset, $postcode);

    $pairs = [];
    $no_target_message = NULL;
    foreach ($constituencies as $constituency) {
      if ($exclusion = $this->getExclusion($constituency)) {
        $exclusion->replaceTokens([], $constituency, $submission_o);
        if (!$no_target_message) {
          $no_target_message = $exclusion->message;
        }
        continue;
      }
      $targets = $constituency['contacts'];
      foreach ($targets as $target) {
        if ($message = $this->getMessage($target, $constituency)) {
          if ($email_override) {
            $target['email'] = $email_override;
          }
          $message->replaceTokens($target, $constituency, $submission_o);
          if ($message->type == 'exclusion') {
            // The first exclusion-message is used.
            if (!$no_target_message) {
              $no_target_message = $message->message;
            }
          }
          else {
            $pairs[] = [$target, $message];
          }
        }
      }
    }

    if (empty($pairs)) {
      watchdog('campaignion_email_to_target', 'The API found no targets (dataset=@dataset, postcode=@postcode).', [
        '@dataset' => $this->options['dataset_name'],
        '@postcode' => $postcode,
      ], WATCHDOG_WARNING);
    }

    if ($no_target_message) {
      $no_target_element = ['#markup' => _filter_autop(check_plain($no_target_message))];
    }
    else {
      $no_target_element = $this->noTargetMessage();
    }

    return [$pairs, $no_target_element];
  }


}

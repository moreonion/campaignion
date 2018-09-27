<?php

namespace Drupal\campaignion_email_to_target;

use Drupal\campaignion_action\ActionBase;
use Drupal\campaignion_action\TypeInterface;
use Drupal\campaignion_email_to_target\Api\Client;
use Drupal\little_helpers\Webform\Submission;

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
   *
   * @param array $target
   *   Target data.
   * @param array $constituency
   *   Constituency data.
   *
   * @return null|\Drupal\campaignion_email_to_target\Message
   *   A message object or NULL if no suitable message was found.
   */
  public function getMessage($target, $constituency) {
    if (empty($target['email'])) {
      // Skip targets without email.
      return NULL;
    }
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
    return ($items ? $items[0] : []) + [
      'dataset_name' => 'mp',
      'users_may_edit' => '',
      'selection_mode' => 'one_or_more',
    ];
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
   * Build selector for querying targets.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   A webform submission object used to determine the selector values.
   *
   * @return string[]
   *   Query parameters used for filtering targets.
   *
   * For the moment the chosen selector as well as the filter mapping is
   * hard-coded.
   *
   * @TODO: Make the selector configurable for datasets with more than one
   * possible selector.
   * @TODO: Make the mapping of form_keys to filter values configurable.
   */
  public function buildSelector(Submission $submission) {
    $dataset = $this->api->getDataset($this->options['dataset_name']);
    $selector_metadata = reset($dataset->selectors);
    $selector = [];
    foreach (array_keys($selector_metadata['filters']) as $filter_name) {
      $selector[$filter_name] = $submission->valueByKey($filter_name);
    }
    if (isset($selector['postcode'])) {
      $selector['postcode'] = preg_replace('/[ -]/', '', $selector['postcode']);
    }
    return $selector;
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
    $email_override = $test_mode ? $submission_o->valueByKey('email') : NULL;

    $pairs = [];
    $no_target_message = NULL;

    $selector = $this->buildSelector($submission_o);
    $contacts = $this->api->getTargets($this->options['dataset_name'], $selector);

    foreach ($contacts as $target) {
      $target += ['constituency' => []];
      $constituency = $target['constituency'];
      unset($target['constituency']);
      if ($exclusion = $this->getExclusion($constituency)) {
        $exclusion->replaceTokens([], $constituency, $submission_o);
        if (!$no_target_message) {
          $no_target_message = $exclusion->message;
        }
        continue;
      }
      if (!$target) {
        // This was an empty contact record only used to send an empty
        // constituency.
        continue;
      }
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
          $pairs[] = [$target, $constituency, $message];
        }
      }
    }

    if (empty($pairs)) {
      watchdog('campaignion_email_to_target', 'The API found no targets (dataset=@dataset, selector=@selector).', [
        '@dataset' => $this->options['dataset_name'],
        '@selector' => drupal_http_build_query($selector),
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

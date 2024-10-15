<?php

namespace Drupal\campaignion_newsletters;

use Drupal\campaignion\CRM\Import\Source\WebformSubmission;
use Drupal\campaignion_opt_in\Values;

/**
 * Special functionality for the newsletter webform component.
 */
class Component {

  /**
   * The component array storing all configuration.
   *
   * @var array
   */
  protected $component;

  /**
   * Flag: Unsubscribe lists for which we don’t know of a subscription.
   *
   * @var bool
   */
  protected $unsubscribeUnknown;

  /**
   * All list_ids for this installation used for lazy-loading.
   *
   * @var int[]
   */
  protected $allListIds = NULL;

  /**
   * Get a new instance from a component array and reading the global config.
   *
   * @param array $component
   *   Component array as stored in `$node->webform['components']`.
   */
  public static function fromComponent(array $component) {
    $unsubscribe_unknown = variable_get_value('campaignion_newsletters_unsubscribe_unknown');
    return new static($component, $unsubscribe_unknown);
  }

  /**
   * Construct a new instance.
   *
   * @param array $component
   *   Component array as stored in `$node->webform['components']`.
   * @param bool $unsubscribe_unknown
   *   Whether to try to unsubscribe even from lists without a subscription.
   */
  public function __construct(array $component, $unsubscribe_unknown) {
    module_load_include('components.inc', 'webform', 'includes/webform');
    webform_component_defaults($component);
    $this->component = $component;
    $this->unsubscribeUnknown = $unsubscribe_unknown;
  }

  /**
   * Create subscriptions from the data in a webform submission.
   *
   * @param string $email
   *   The email address found in this submission.
   * @param \Drupal\campaignion\CRM\Import\Source\WebformSubmission $s
   *   The webform submission that is being submitted.
   *
   * @return \Drupal\campaignion_newsletters\Subscription[]
   *   A list of subscriptions.
   */
  public function getSubscriptions($email, WebformSubmission $s) {
    if ($value = $s->valuesByCid($this->component['cid'])) {
      $value = Values::removePrefix($value);
      if ($value == 'opt-in') {
        return $this->subscribe($email, $s);
      }
      elseif ($value == 'opt-out') {
        return $this->unsubscribe($email);
      }
    }
    return [];
  }

  /**
   * Unsubscribe the email address from configured/all newsletter lists.
   *
   * @param string $email
   *   The email address to unsubscribe.
   *
   * @return \Drupal\campaignion_newsletters\Subscription[]
   *   A list of (un)subscriptions.
   */
  public function unsubscribe($email) {
    // Determine the set of subscriptions to revoke.
    $all_lists = !empty($this->component['extra']['optout_all_lists']);
    $selected_lists_ids = array_keys(array_filter($this->component['extra']['lists']));
    $list_ids_to_unsubscribe = $all_lists ? $this->getAllListIds() : $selected_lists_ids;
    $known_subscriptions = [];
    foreach (Subscription::byEmail($email) as $subscription) {
      $known_subscriptions[$subscription->list_id] = $subscription;
    }
    $subscriptions = array_filter(array_map(function($list_id) use ($email, $known_subscriptions) {
      $default = $this->unsubscribeUnknown ? Subscription::byData($list_id, $email, []) : NULL;
      return $known_subscriptions[$list_id] ?? $default;
    }, $list_ids_to_unsubscribe));
    // Mark the selected subscriptions as deleted.
    foreach ($subscriptions as $subscription) {
      $subscription->delete = TRUE;
    }
    return $subscriptions;
  }

  /**
   * Subscribe an email address to all configured lists.
   *
   * @param string $email
   *   The email address to subscribe.
   * @param \Drupal\campaignion\CRM\Import\Source\WebformSubmission $source
   *   The importer source for further CRM action.
   *
   * @return \Drupal\campaignion_newsletters\Subscription[]
   *   A list of subscriptions.
   */
  public function subscribe($email, WebformSubmission $source) {
    $subscriptions = [];
    $lists = array_keys(array_filter($this->component['extra']['lists']));
    foreach ($lists as $list_id) {
      $subscription = Subscription::byData($list_id, $email, [
        'source' => $source,
        'components' => [$this->component],
      ]);
      $subscriptions[] = $subscription;
    }
    return $subscriptions;
  }

  /**
   * Set the array of all list IDs. Usually only used for testing.
   *
   * @param int[] $list_ids
   *   Array of list IDs.
   */
  public function setAllListIds(array $list_ids) {
    $this->allListIds = $list_ids;
  }

  /**
   * Get all list IDs.
   *
   * @return int[]
   *   Array of list IDs.
   */
  public function getAllListIds() {
    if (is_null($this->allListIds)) {
      $list_ids = [];
      foreach (array_keys(NewsletterList::options()) as $list_id) {
        $list_ids[] = $list_id;
      }
      $this->setAllListIds($list_ids);
    }
    return $this->allListIds;
  }

  /**
   * Remove list from all components.
   *
   * @param int $list_id
   *   The list_id that is to be removed.
   */
  public static function pruneList($list_id) {
    $node_controller = entity_get_controller('node');

    $result = db_select('webform_component', 'c')
      ->fields('c', ['nid', 'cid'])
      ->condition('type', 'opt_in')
      ->execute();
    foreach ($result as $row) {
      $node = node_load($row->nid);
      $component = $node->webform['components'][$row->cid];
      if (!empty($component['extra']['lists'][$list_id])) {
        unset($component['extra']['lists'][$list_id]);
        webform_component_update($component);
        $node_controller->resetCache([$row->nid]);
        $left = array_filter($component['extra']['lists']);
        if ($node->status) {
          watchdog('campaignion_newsletters', 'Removed list from published node#%nid(%type) “%title” lists left: %lists', [
            '%nid' => $node->nid,
            '%type' => $node->type,
            '%title' => $node->title,
            '%lists' => implode(', ', $left),
          ], $left ? WATCHDOG_NOTICE : WATCHDOG_WARNING);
        }
      }
    }
  }

}

<?php

namespace Drupal\campaignion_newsletters;

use Drupal\campaignion\CRM\Import\Source\WebformSubmission;
use Drupal\little_helpers\ArrayConfig;

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
    $unsubscribe_unknown = variable_get_value('campaignion_newsletters_opt_out_unknown');
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
    ArrayConfig::mergeDefaults($component, webform_component_invoke('newsletter', 'defaults'));
    $this->component = $component;
    $this->unsubscribeUnknown = $unsubscribe_unknown;
  }

  /**
   * Unsubscribe the email address from configured/all newsletter lists.
   *
   * @param string $email
   *   The email address to unsubscribe.
   */
  public function unsubscribe($email) {
    $all_lists = !empty($this->component['extra']['optout_all_lists']);
    if ($all_lists) {
      if ($this->unsubscribeUnknown) {
        foreach ($this->getAllListIds() as $list_id) {
          $subscription = Subscription::byData($list_id, $email);
          if ($subscription->isNew()) {
            $subscription->queueUnsubscribe();
          }
        }
      }
      foreach (Subscription::byEmail($email) as $subscription) {
        $subscription->delete();
      }
    }
    else {
      $lists = $this->component['extra']['lists'];
      foreach (Subscription::byEmail($email) as $subscription) {
        if (!empty($lists[$subscription->list_id])) {
          $subscription->delete();
        }
      }
    }
  }

  /**
   * Subscribe an email address to all configured lists.
   *
   * @param string $email
   *   The email address to subscribe.
   * @param \Drupal\campaignion\CRM\Import\Source\WebformSubmission $source
   *   The importer source for further CRM action.
   */
  public function subscribe($email, WebformSubmission $source) {
    $extra = $this->component['extra'];
    $lists = array_keys(array_filter($extra['lists']));
    foreach ($lists as $list_id) {
      $subscription = Subscription::byData($list_id, $email, [
        'source' => $source,
        'needs_opt_in' => !$extra['opt_in_implied'],
        'send_welcome' => (bool) $extra['send_welcome'],
        'optin_statement' => $extra['optin_statement'],
        'optin_info' => FormSubmission::fromWebformSubmission($source),
      ]);
      $subscription->save();
    }
  }

  /**
   * Set the array of all list IDs. Usually only used for testing.
   *
   * @param int[]
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
      foreach (NewsletterList::listAll() as $l) {
        $list_ids[] = $l;
      }
      $this->setAllListIds($list_ids);
    }
    return $this->allListIds;
  }

}

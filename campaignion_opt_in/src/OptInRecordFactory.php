<?php

namespace Drupal\campaignion_opt_in;

use Drupal\campaignion_activity\ActivityBase;

/**
 * Generate opt-in records for an activity.
 */
class OptInRecordFactory {

  /**
   * The activity we are adding opt-in records to.
   *
   * @var Drupal\campaignion_activity\ActivityBase
   */
  protected $activity;

  /**
   * Create a factory for an activity.
   *
   * @param Drupal\campaignion_activity\ActivityBase $activity
   * The activity for which opt-in records will be added.
   */
  public function __construct(ActivityBase $activtiy) {
    $this->activity = $activtiy;
  }

  /**
   * Add a new opt-in record.
   *
   * @param array $component
   *   The webform component that is being processed.
   * @param mixed $value
   *   The submission value for the webform component.
   */
  public function recordOptIn($component, $value) {
    // TODO: correct prefixed values
    if (in_array($value, [Values::OPT_IN, Values::OPT_OUT])) {
      db_insert('campaignion_opt_in')->fields([
        'activity_id' => $this->activity->activity_id,
        'operation' => $value,
        'channel' => $component['extra']['channel'],
        'statement' => $component['extra']['optin_statement'],
      ])->execute();
    }
  }

  /**
   * Add a new opt-in record based on a newsletter component.
   *
   * @param array $component
   *   The webform component that is being processed.
   * @param mixed $value
   *   The submission value for the webform component.
   */
  public function recordNewsletterSubscription($component, $value) {
    // TODO: correct prefixed values
    if (in_array($value, ['subscribed', 'unsubscribed'])) {
      db_insert('campaignion_opt_in')->fields([
        'activity_id' => $this->activity->activity_id,
        'operation' => $value == 'subscribed' ? Values::OPT_IN : Values::OPT_OUT,
        'channel' => 'email',
        'statement' => $component['extra']['optin_statement'],
      ])->execute();
    }
  }

}

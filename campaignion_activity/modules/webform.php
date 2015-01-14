<?php

function webform_campaignion_activity_type_info() {
  $info['webform_submission'] = 'Drupal\campaignion\Activity\WebformSubmissionType';
}

/**
 * Helper function to log a webform activity.
 */
function _campaignion_activity_webform_log($node, $submission) {
  try {
    $activity = \Drupal\campaignion\Activity\WebformSubmission::fromSubmission($node, $submission);
    $activity->save();
  } catch (Exception $e) {
    watchdog('campaignion_activity', 'Error when trying to log activity: !message', array('!message' => $e->getMessage()), WATCHDOG_WARNING);
  }
}

/**
 * Implements hook_webform_submission_insert().
 */
function campaignion_activity_webform_submission_insert($node, $submission) {
  if ($submission->is_draft) {
    return;
  }
  _campaignion_activity_webform_log($node, $submission);
}

/**
 * Implements hook_webform_submission_update().
 */
function campaignion_activity_webform_submission_update($node, $submission) {
  if ($submission->is_draft) {
    return;
  }
  $was_draft = db_select('webform_submissions', 's')
    ->fields('s', array('sid'))
    ->condition('s.sid', $submission->sid)
    ->execute()
    ->fetchField();
  if ($was_draft) {
    _campaignion_activity_webform_log($node, $submission);
  }
}

/**
 * Implements hook_webform_confirm_email_email_confirmed().
 */
function campaignion_activity_webform_confirm_email_email_confirmed($node, $submission) {
  if (!($activity = \Drupal\campaignion\Activity\WebformSubmission::bySubmission($node, $submission))) {
    watchdog('campaignion_activity', 'Trying to confirm a not yet logged submission: !nid, !sid', array('!nid' => $node->nid, '!sid' => $submission->sid), WATCHDOG_WARNING);
    try {
      $activity = \Drupal\campaignion\Activity\WebformSubmission::fromSubmission($node, $submission);
    } catch (Exception $e) {
      watchdog('campaignion_activity', 'Error when trying to log activity: !message', array('!message' => $e->getMessage()), WATCHDOG_WARNING);
    }
  }
  $activity->confirmed = time();
  $activity->save();
}


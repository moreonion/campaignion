<?php

function webform_campaignion_activity_type_info() {
  $info['webform_submission'] = 'Drupal\campaignion\Activity\WebformSubmissionType';
}

/**
 * Implements hook_webform_submission_insert().
 */
function campaignion_activity_webform_submission_insert($node, $submission) {
  try {
    $activity = \Drupal\campaignion\Activity\WebformSubmission::fromSubmission($node, $submission);
    $activity->save();
  } catch (Exception $e) {
    watchdog('campaignion_activity', 'Error when trying to log activity: !message', array('!message' => $e->getMessage()), WATCHDOG_WARNING);
  }
}

function campaignion_activity_email_confirmed($node, $submission) {
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


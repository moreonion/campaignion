<?php

function webform_campaignion_activity_type_info() {
  $info['webform_submission'] = 'Drupal\campaignion\Activity\WebformSubmission';
}

/**
 * Implements hook_webform_submission_insert().
 */
function campaignion_activity_webform_submission_insert($node, $submission) {
  $s = new Drupal\little_helpers\WebformSubmission($node, $submission);
  $sql = <<<SQL
SELECT entity_id
FROM field_data_redhen_contact_email
WHERE redhen_contact_email_value = :email
SQL;

  $contact_id = db_query($sql, array(':email' => $s->valueByKey('email_address')))->fetchField();
  if (!$contact_id) {
    // create contact.
  }

  $activity = \Drupal\campaignion\Activity\WebformSubmission::fromSubmission($node, $submission);
  $activity->save();
}

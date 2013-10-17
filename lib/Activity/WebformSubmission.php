<?php

namespace Drupal\campaignion\Activity;

class WebformSubmission extends \Drupal\campaignion\Activity {
  protected $type = 'webform_submission';

  public static function fromSubmission($node, $submission) {
    /* @TODO put contact creation into it's own class.
       Use something like:
       $contact_id = \Drupal\campaignion\Contact::idFromSubmission($node, $submission);
    */
    $s = new \Drupal\little_helpers\WebformSubmission($node, $submission);
    $sql = <<<SQL
SELECT entity_id
FROM field_data_redhen_contact_email
WHERE redhen_contact_email_value = :email
SQL;

    $contact_id = db_query($sql, array(':email' => $s->valueByKey('email_address')))->fetchField();
    if (!$contact_id) {
      // create contact.
    }
  }

  protected function insert() {
    parent::insert();
    db_insert('campaignion_activity_webform')
      ->fields($this->value(array('activity_id', 'nid', 'sid')))
      ->execute();
  }
  
  protected function update() {
    parent::update();
    db_update('campaignion_activity_webform')
      ->fields($this->value(array('nid', 'sid')))
      ->condition('activity_id', $this->activity_id)
      ->execute();
  }
}

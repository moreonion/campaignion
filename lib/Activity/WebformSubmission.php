<?php

namespace Drupal\campaignion\Activity;

class WebformSubmission extends \Drupal\campaignion\Activity {
  protected $type = 'webform_submission';

  public $sid;
  public $nid;
  
  public static function bySubmission($node, $submission) {
    $sql = <<<SQL
SELECT * FROM {campaignion_activity}
INNER JOIN {campaignion_activity_webform} USING (activity_id)
WHERE nid=:nid, sid=:sid
SQL;
    $result = db_query($sql, array(':nid' => $node->nid, ':sid' => $submission->sid));
    return $result->fetchObject(get_called_class());
  }

  public static function fromSubmission($node, $submission) {
    $contact_id = \Drupal\campaignion\Contact::idFromSubmission($node, $submission, 'contact');

    $data = array(
      'contact_id' => $contact_id,
      'nid' => $node->nid,
      'sid' => $submission->sid,
    );
    return new static($data);
  }

  protected function insert() {
    parent::insert();
    db_insert('campaignion_activity_webform')
      ->fields($this->values(array('activity_id', 'nid', 'sid')))
      ->execute();
  }
  
  protected function update() {
    parent::update();
    db_update('campaignion_activity_webform')
      ->fields($this->values(array('nid', 'sid')))
      ->condition('activity_id', $this->activity_id)
      ->execute();
  }
}

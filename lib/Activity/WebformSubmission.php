<?php

namespace Drupal\campaignion\Activity;

class WebformSubmission extends \Drupal\campaignion\Activity {
  protected $type = 'webform_submission';

  public $sid;
  public $nid;

  public static function bySubmission($node, $submission) {
    $query = static::buildJoins();
    $query->condition('aw.nid', $node->nid)
          ->condition('aw.sid', $submission->sid);
    return $query->execute()->fetchObject(get_called_class());
  }

  protected static function buildJoins() {
    $query = db_select('campaignion_activity', 'a')
      ->fields('a');
    $query->innerJoin('campaignion_activity_webform', 'aw', 'aw.activity_id=a.activity_id');
    $query->fields('aw');
    return $query;
  }

  public static function fromSubmission($node, $submission, $data = array()) {
    $contact_id = \Drupal\campaignion\Contact::idFromSubmission($node, $submission, 'contact');

    $data = array(
      'contact_id' => $contact_id,
      'nid' => $node->nid,
      'sid' => $submission->sid,
    ) + $data;
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

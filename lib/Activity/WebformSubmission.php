<?php

namespace Drupal\campaignion\Activity;

class WebformSubmission extends \Drupal\campaignion\Activity {
  protected $type = 'webform_submission';

  public $sid;
  public $nid;
  public $confirmed = NULL;

  public static function bySubmission($node, $submission) {
    return static::byNidSid($node->nid, $submission->sid);
  }

  public static function byNidSid($nid, $sid) {
    $query = static::buildJoins();
    $query->condition('aw.nid', $nid)
          ->condition('aw.sid', $sid);
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
    $contact_id = \Drupal\campaignion\Contact::idFromSubmission($node, $submission);

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
      ->fields($this->values(array('activity_id', 'nid', 'sid', 'confirmed')))
      ->execute();
  }

  protected function update() {
    parent::update();
    db_update('campaignion_activity_webform')
      ->fields($this->values(array('nid', 'sid', 'confirmed')))
      ->condition('activity_id', $this->activity_id)
      ->execute();
  }

  // @TODO: Use full objects instead of nid/sid by default instead of always loading them.
  public function node() {
    return node_load($this->nid);
  }

  public function submission() {
    return \Drupal\little_helpers\WebformSubmission::load($this->nid, $this->sid);
  }
}

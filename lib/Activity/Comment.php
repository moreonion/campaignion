<?php

namespace Drupal\campaignion\Activity;

class Comment extends \Drupal\campaignion\Activity {
  protected $type = 'comment';

  public $cid;
  public $nid;

  public static function fromComment($comment, $data = array()) {
    if (empty($comment->mail) && !empty($comment->uid)) {
      $user = user_load($comment->uid);
      $comment->mail = $user->mail;
    }
    if (empty($comment->mail)) {
      return NULL;
    }
    $contact_id = \Drupal\campaignion\Contact::idFromBasicData($comment->mail);
    $data = array(
      'contact_id' => $contact_id,
      'nid' => $comment->nid,
      'cid' => $comment->cid,
    );
    return new static($data);
  }
  
  protected function insert() {
    parent::insert();
    db_insert('campaignion_activity_comment')
      ->fields($this->values(array('activity_id', 'nid', 'cid')))
      ->execute();
  }

  protected function update() {
    parent::update();
    db_update('campaignion_activity_comment')
      ->fields($this->values(array('nid', 'cid')))
      ->condition('activity_id', $this->activity_id)
      ->execute();
  }
}

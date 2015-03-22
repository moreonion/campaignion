<?php

namespace Drupal\campaignion_manage\BulkOp;

class SupporterTagBatch extends BatchBase {
  protected $tids;
  protected $tag;
  public function __construct(&$data) {
    $this->tids = $data['tids'];
    $this->tag = (bool) $data['tag'];
  }
  public function apply($contact, &$results) {
    if ($this->tag) {
      $tids = $this->tids;
      // add tags
      foreach ($contact->supporter_tags['und'] as $already_set) {
        unset($tids[$already_set['tid']]);
      }
      foreach ($tids as $tid) {
        $contact->supporter_tags['und'][] = array('tid' => $tid);
      }
    }
    else {
      // delete tags
      $tids = array();
      foreach ($contact->supporter_tags['und'] as $tag_index => $tag) {
        if (isset($tids[$tag['tid']])) {
          unset($contact->supporter_tags['und'][$tag_index]);
          $tids[$tag['tid']] = $tag['tid'];
        }
      }
    }
    if (!empty($tids)) {
      try {
        $contact->save();
      } catch (Exception $e) {
        $results['failed_contacts'][$contact->contact_id] = $e->getMessage();
      }
    }
  }
}

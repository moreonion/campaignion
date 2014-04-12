<?php

namespace Drupal\campaignion_manage\Query;

class Content extends Base {
  public function __construct() {
    $query = db_select('node', 'n');
    $query->innerJoin('users', 'u', 'u.uid = n.uid');
    $query->fields('n', array('nid', 'title', 'type', 'language', 'status', 'uid'))
      ->condition('n.type', 'thank_you_page', '!=')
      ->fields('u', array('name'))
      ->where('n.nid = n.tnid OR n.tnid = 0')
      ->orderBy('n.changed', 'DESC');

    parent::__construct($query);
  }

  public function modifyResult(&$rows) {
    if (empty($rows)) {
      return;
    }
    $rows_by_nid = array();
    foreach ($rows as $row) {
      $row->translations = array();
      $rows_by_nid[$row->nid] = $row;
    }

    $sql = <<<SQL
SELECT n.tnid, n.nid, n.title, n.type, n.language, n.status, n.uid, u.name
FROM {node} n 
  INNER JOIN {users} u ON u.uid=n.uid
WHERE n.tnid IN(:nids) AND n.tnid!=n.nid
ORDER BY n.language
SQL;
    $result = db_query($sql, array(':nids' => array_keys($rows_by_nid)));
    foreach ($result as $row) {
      $rows_by_nid[$row->tnid]->translations[$row->language] = $row;
    }
  }

  public function count() {
    $query = clone $this->filtered();
    $query->innerJoin('node', 'c', 'c.nid=n.nid OR (n.tnid!=0 AND c.tnid=n.tnid)');
    return $query->countQuery()->execute()->fetchField();
  }
}

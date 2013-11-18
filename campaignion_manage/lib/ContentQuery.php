<?php

namespace Drupal\campaignion_manage;

class ContentQuery {
  protected $query;

  public function __construct() {
    $this->build();
  }

  public function build() {
    $query = db_select('node', 'n');
    $query->innerJoin('users', 'u', 'u.uid = n.uid');
    $query->fields('n', array('nid', 'title', 'type', 'language', 'status', 'uid'))
      ->fields('u', array('name'))
      ->where('n.nid = n.tnid OR n.tnid = 0')
      ->orderBy('n.changed', 'DESC');
    $this->query = $query;
  }

  public function execute() {
    $rows = $this->query->execute()->fetchAll();
    $this->modifyResult($rows);
    return $rows;
  }

  public function page($size) {
    $this->query = $this->query->extend('PagerDefault')->limit($size);
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
WHERE n.tnid IN(:nids)
ORDER BY n.language
SQL;
    $result = db_query($sql, array(':nids' => array_keys($rows_by_nid)));
    foreach ($result as $row) {
      $rows_by_nid[$row->tnid]->translations[$row->language] = $row;
    }
  }

  public function ensureTable($alias) {
  }

  public function getQuery() {
    return $this->query;
  }
}

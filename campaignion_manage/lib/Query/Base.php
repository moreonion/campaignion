<?php

namespace Drupal\campaignion_manage\Query;

abstract class Base {
  protected $query;
  protected $filter;

  public function setFilter($filter) {
    $this->filter = $filter;
  }

  public function execute() {
    $this->filter->applyFilters($this);
    $rows = $this->query->execute()->fetchAll();
    $this->modifyResult($rows);
    return $rows;
  }

  public function page($size) {
    $this->query = $this->query->extend('PagerDefault')->limit($size);
  }

  public function modifyResult(&$rows) {
  }

  public function ensureTable($alias) {
  }

  public function getQuery() {
    return $this->query;
  }
}

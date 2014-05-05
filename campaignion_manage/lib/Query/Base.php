<?php

namespace Drupal\campaignion_manage\Query;

abstract class Base {
  protected $_query;
  protected $query;
  protected $filtered;
  protected $paged;

  public function __construct(\SelectQuery $query) {
    $this->_query = $query;
    $this->reset();
  }

  public function execute() {
    $rows = $this->paged->execute()->fetchAll();
    $this->modifyResult($rows);
    return $rows;
  }

  public function setPage($size) {
    $this->paged = clone $this->filtered;
    $this->paged = $this->paged->extend('PagerDefault')->limit($size);
  }

  public function reset() {
    $this->query = clone $this->_query;
    $this->filtered = clone $this->_query;
    $this->paged = clone $this->_query;
  }

  public function modifyResult(&$rows) {
  }

  public function ensureTable($alias) {
  }

  public function query() {
    return $this->query;
  }

  public function filtered() {
    return $this->filtered;
  }

  public function paged() {
    return $this->paged;
  }

  public function count() {
    return $this->filtered->countQuery()->execute()->fetchField();
  }

  public function __sleep() {
    $this->query = NULL;
    $this->filtered = NULL;
    $this->paged = NULL;
    return array('_query');
  }

  public function __wakeup() {
    $this->reset();
  }

  public function filter($form) {
    $form->applyFilters($this->filtered);
  }
}

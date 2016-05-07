<?php

namespace Drupal\campaignion_newsletters;

class ApiError extends \Exception {
  public $backend = '';
  public $variables = array();
  public $link;
  public function __construct($backend, $message = '', $variables = array(), $code = 0, $link = NULL, \Exception $previous = NULL) {
    $this->backend = $backend;
    $message = format_string($message, $variables + ['@code' => $code]);
    $this->link = $link;
    parent::__construct($message, $code, $previous);
  }
  public function log() {
    \watchdog($this->backend, $this->message, [], WATCHDOG_ERROR, $this->link);
  }
}

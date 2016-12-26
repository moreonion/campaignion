<?php

namespace Drupal\campaignion_newsletters_mailchimp\Rest;

class HttpError extends \Exception {

  public $result;

  public function __construct($result) {
    $msg = "HTTP {$result->code}: {$result->status_message}";
    parent::__construct($msg, $result->code);
    $this->result = $result;
  }

}

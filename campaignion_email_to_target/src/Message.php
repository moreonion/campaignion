<?php

namespace Drupal\campaignion_email_to_target;

use Drupal\little_helpers\Webform\Submission;

/**
 * Common datastructure for handling protest messages.
 */
class Message {
  public $type;
  public $to;
  public $from;
  public $subject;
  public $header;
  public $message;
  public $footer;
  public $display;
  protected $tokenEnabledFields = ['to', 'from', 'subject', 'header', 'message', 'footer', 'display'];

  public function __construct($data) {
    $data += [
      'from' => '[submission:values:first_name] [submission:values:last_name] <[submission:values:email]>',
      'to' => '[email-to-target:contact.title] [email-to-target:contact.first_name] [email-to-target:contact.last_name] <[email-to-target:contact.email]>',
      'display' => '[email-to-target:contact.display_name]',
    ];
    foreach ($data as $k => $v) {
      $this->$k = $v;
    }
  }

  public static function fromTemplate(MessageTemplate $t) {
    $data = [
      'type' => $t->type,
      'subject' => $t->subject,
      'header' => $t->header,
      'message' => $t->message,
      'footer' => $t->footer,
    ];
    return new static($data);
  }

  public function replaceTokens($target = NULL, $submission = NULL, $clear = FALSE) {
    if (empty($target['display_name']) && !empty($target['salutation'])) {
      $target['display_name'] = $target['salutation'];
    }
    $data['email-to-target'] = $target;
    $data['webform-submission'] = $submission;
    if ($submission instanceof Submission) {
      $data['node'] = $submission->node;
    }
    // It's ok to not sanitize values here. We will sanitize them later
    // when it's clear whether we use it in a plain text email (no escaping)
    // or in HTML output (check_plain).
    $options['sanitize'] = FALSE;
    $options['clear'] = $clear;
    foreach ($this->tokenEnabledFields as $f) {
      $this->{$f} = token_replace($this->{$f}, $data, $options);
    }
  }

  public function toArray() {
    $r = [];
    foreach (['type', 'to', 'from', 'subject', 'header', 'message', 'footer'] as $f) {
      $r[$f] = $this->$f;
    }
    return $r;
  }
}

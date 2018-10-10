<?php

namespace Drupal\campaignion_email_to_target;

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
  protected $tokenEnabledFields = ['to', 'from', 'subject', 'header', 'message', 'footer'];

  public function __construct($data) {
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
    return new static($data + [
      'from' => '[submission:values:first_name] [submission:values:last_name] <[submission:values:email]>',
      'to' => '[email-to-target:contact.title] [email-to-target:contact.first_name] [email-to-target:contact.last_name] <[email-to-target:contact.email]>',
    ]);
  }

  public function replaceTokens($target = NULL, $submission = NULL) {
    $data['email-to-target'] = $target;
    $data['webform-submission'] = $submission;
    // It's ok to not sanitize values here. We will sanitize them later
    // when it's clear whether we use it in a plain text email (no escaping)
    // or in HTML output (check_plain).
    $options['sanitize'] = FALSE;
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

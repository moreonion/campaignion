<?php

namespace Drupal\campaignion_email_to_target;

/**
 * Common datastructure for handling protest messages.
 */
class Message extends MessageTemplateInstance {
  public $toName;
  public $toAddress;
  public $fromName;
  public $fromAddress;
  public $subject;
  public $header;
  public $message;
  public $footer;
  public $display;
  protected $tokenEnabledFields = [
    'toName',
    'toAddress',
    'fromName',
    'fromAddress',
    'subject',
    'header',
    'message',
    'footer',
    'display',
  ];

  /**
   * Create a message instance by passing the data as object or array.
   *
   * @param mixed $data
   *   The data to initialize the message with.
   */
  public function __construct($data) {
    $data += [
      'fromName' => '[submission:values:first_name] [submission:values:last_name]',
      'fromAddress' => '[submission:values:email]',
      'toName' => '[email-to-target:title] [email-to-target:first_name] [email-to-target:last_name]',
      'toAddress' => '[email-to-target:email]',
      'display' => '[email-to-target:display_name]',
    ];
    parent::__construct($data);
  }

  /**
   * Quote strings for use in address headers.
   *
   * Encode UTF-8 characters as needed then quote double quotes.
   */
  protected function quoteMail($string): string {
    return addcslashes(mb_encode_mimeheader($string, 'UTF-8', 'Q'), '"\\');
  }

  /**
   * Get full To: address.
   */
  public function to() {
    return '"' . trim($this->quoteMail($this->toName)) . '" <' . $this->toAddress . '>';
  }

  /**
   * Get full From: address.
   */
  public function from() {
    return '"' . trim($this->quoteMail($this->fromName)) . '" <' . $this->fromAddress . '>';
  }

  /**
   * Return an with all public variables.
   *
   * @return array
   *   Associative array containing all public variables.
   */
  public function toArray() {
    return call_user_func('get_object_vars', $this);
  }

}

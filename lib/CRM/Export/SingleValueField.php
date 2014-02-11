<?php

namespace Drupal\campaignion\CRM\Export;

use Drupal\campaignion\Contact;

class SingleValueField {
  public function __construct(Contact $contact, $key) {
    $this->contact = $contact;
    $this->key = $key;
  }

  public function value() {
    return isset($this->contact->{$this->key}) ? $this->contact->{$this->key} : NULL;
  }
}

<?php

namespace Drupal\campaignion\CRM\Export;

class WrapperField {
  protected $wrappedContact;
  protected $key;
  public function __construct(\EntityMetadataWrapper $wrappedContact, $key) {
    $this->key = $key;
    $this->wrappedContact = $wrappedContact;
  }

  public function value() {
    if ($this->wrappedContact->__isset($this->key)) {
      $value = $this->wrappedContact->{$this->key}->value();
      if (is_array($value) && isset($value[0]) && count($value) == 1) {
        return $value[0];
      }
      return $value;
    }
    else {
      return NULL;
    }
  }
}

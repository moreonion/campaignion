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
    $value = $this->wrappedContact->{$this->key}->value();
    if (is_array($value)) {
      return $value[0];
    }
    return $value;
  }
}

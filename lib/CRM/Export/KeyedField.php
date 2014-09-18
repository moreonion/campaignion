<?php

namespace Drupal\campaignion\CRM\Export;

class KeyedField extends WrapperField {
  protected $subkey;
  public function __construct($key, $subkey) {
    parent::__construct($key);
    $this->subkey = $subkey;
  }

  public function value() {
    if (($value = parent::value(0)) && isset($value[$this->subkey])) {
      return $value[$this->subkey];
    }
  }
}

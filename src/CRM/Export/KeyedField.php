<?php

namespace Drupal\campaignion\CRM\Export;

class KeyedField extends WrapperField {
  protected $subkey;
  public function __construct($key, $subkey) {
    parent::__construct($key);
    $this->subkey = $subkey;
  }

  public function value($delta = NULL) {
    if (($values = parent::value($delta))) {
      foreach ($values as $value) {
        if (!empty($value[$this->subkey])) {
          return $value[$this->subkey];
        }
      }
    }
  }
}

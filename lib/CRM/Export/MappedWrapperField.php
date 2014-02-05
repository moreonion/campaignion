<?php

namespace Drupal\campaignion\CRM\Export;

class MappedWrapperField extends WrapperField {
  protected $map;
  public function __construct(\EntityMetadataWrapper $wrappedContact, $key, $map) {
    parent::__construct($wrappedContact, $key);
    $this->map = $map;
  }

  public function value() {
    $value = parent::value();
    if (isset($this->map[$value])) {
      return $this->map[$value];
    }
  }
}

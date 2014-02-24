<?php

namespace Drupal\campaignion\CRM\Export;

class DateField extends WrapperField {
  protected $format;
  public function __construct(\EntityMetadataWrapper $wrappedContact, $key, $format) {
    parent::__construct($wrappedContact, $key);
    $this->format = $format;
  }
  public function value() {
    if ($timestamp = parent::value()) {
      return strftime($this->format, $timestamp);
    }
  }
}

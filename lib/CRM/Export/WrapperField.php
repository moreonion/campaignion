<?php

namespace Drupal\campaignion\CRM\Export;

use \Drupal\campaignion\CRM\ExporterInterface;

class WrapperField implements ExportMapperInterface {
  protected $exporter;
  protected $key;
  public function __construct($key) {
    $this->key = $key;
  }

  public function value() {
    $w = $this->exporter->getWrappedContact();
    if ($w->__isset($this->key)) {
      $value = $w->{$this->key}->value();
      if (is_array($value) && isset($value[0]) && count($value) == 1) {
        return $value[0];
      }
      return $value;
    }
    else {
      return NULL;
    }
  }

  public function setExporter(ExporterInterface $exporter) {
    $this->exporter = $exporter;
  }
}

<?php

namespace Drupal\campaignion_newsletters_cleverreach;

use \Drupal\campaignion\CRM\Export\WrapperField;

class TagExporter extends WrapperField {

  public function value() {
    $tags = $this->wrappedContact->{$this->key}->value();
    $names = array();
    foreach ($tags as $tag) {
      $names[] = str_replace(',', '', $tag->name);
    }
    $result = implode(',', $names);
    return empty($result) ? '' : ',' . $result . ',';
  }
}

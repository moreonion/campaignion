<?php

namespace Drupal\campaignion\CRM\Export;

use \Drupal\campaignion\CRM\Export\WrapperField;

class TagField extends WrapperField {

  public function value() {
    if ($this->wrappedContact->__isset($this->key)) {
      $tags = $this->wrappedContact->{$this->key}->value();
      $names = array();
      foreach ($tags as $tag) {
        $names[] = str_replace(',', '', $tag->name);
      }
      return implode(',', $names);
    }
    else {
      return NULL;
    }
  }
}
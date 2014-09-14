<?php

namespace Drupal\campaignion\CRM\Export;

class TagsField extends WrapperField {
  protected $filterable;
  public function __construct(\EntityMetadataWrapper $wrappedContact, $key, $filterable = FALSE) {
    parent::__construct($wrappedContact, $key);
    $this->filterable = $filterable;
  }

  public function value() {
    $w = $this->wrappedContact;
    $tags = $w->{$this->key}->value();
    $names = array();
    foreach ($tags as $tag) {
      $names[] = str_replace(',', '', $tag->name);
    }
    $result = implode(',', $names);
    if ($this->filterable && $result) {
      $result = ",$result,";
    }
    return $result;
  }
}

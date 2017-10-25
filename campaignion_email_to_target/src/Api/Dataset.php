<?php

namespace Drupal\campaignion_email_to_target\Api;

/**
 * Metadata for Email-To-Target datasets.
 */
class Dataset {
  public $key;
  public $title;
  public $description;
  public $attributes;
  public $isCustom;

  public function __construct($key, $title='', $description='', $attributes=[], $is_custom=FALSE) {
    $this->key = $key;
    $this->title = $title;
    $this->description = $description;
    $this->attributes = $attributes;
    $this->isCustom = $is_custom;
  }

  public static function fromArray($data) {
    $attributes = [];
    foreach ($data['attributes'] as $attribute_data) {
      $attributes[] = Attribute::fromArray($attribute_data);
    }
    return new static($data['key'], $data['title'], $data['description'], $attributes, $data['is_custom']);
  }

}

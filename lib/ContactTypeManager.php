<?php

namespace Drupal\campaignion;

class ContactTypeManager {
  protected $types;
  private static $instance = NULL;

  public static function instance() {
    if (!static::$instance) {
      static::$instance = new static();
    }
    return static::$instance;
  }

  public static function info() {
    $info = \module_invoke_all('campaignion_contact_type_info');
    \drupal_alter('campaignion_contact_type_info', $info);
    return $info;
  }

  public function __construct() {
    $this->types = array();
    foreach (self::info() as $type => $class) {
      $this->types[$type] = new $class();
    }

    $entity_info = \entity_get_info('redhen_contact');
    $default = array();
    foreach ($entity_info['bundles'] as $name => $info) {
      $default[$name] = NULL;
    }
    $this->types += $default;
  }

  public function importer($source, $type = NULL) {
    return $this->getType($type)->importer($source);
  }

  public function exporter($target, $type = NULL) {
    return $this->getType($type)->exporter($target);
  }

  public function exporterByEmail($email, $target = NULL, $type = NULL) {
    $contact = Contact::byEmail($email, $type);
    $exporter = $this->getType($contact->type)->exporter($target);
    if ($exporter && $contact) {
      $exporter->setContact($contact);
      return $exporter;
    }
  }

  public function getType($type) {
    if (!$type) {
      $type =  Contact::defaultType();
    }
    if (isset($this->types[$type])) {
      return $this->types[$type];
    }
    throw new Exceptions\UndefinedContactTypeException($type);
  }
}

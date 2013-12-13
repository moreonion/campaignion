<?php

namespace Drupal\campaignion\CRM\Import\Field;

class EmailBulk extends Field {
  protected $bulkSource = array();
  public function __construct($field, $source, $bulkSource) {
    parent::__construct($field, $source);
    $this->bulkSource = is_array($bulkSource) ? $bulkSource : array($bulkSource);
  }

  public function getValue($source) {
    $value['value'] = self::valueFromSource($source, $this->source);
    if (!$value['value']) {
      return;
    }
    $value['bulk'] = (bool) self::valueFromSource($source, $this->bulkSource);
    return $value;
  }

  public function storeValue($entity, $value) {
    return TRUE;
  }

  public function setValue($entity, $value) {
    $emails = $entity->redhen_contact_email->value();
    foreach ($emails as &$email) {
      if ($email['value'] == $value['value'] && $email['bulk'] != $value['bulk']) {
        $email['bulk'] = $value['bulk'];
        $set = TRUE;
        break;
      }
    }
    if ($set) {
      $entity->redhen_contact_email->set($emails);
    }
    return $set;
  }
}

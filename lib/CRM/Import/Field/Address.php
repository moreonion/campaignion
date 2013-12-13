<?php

namespace Drupal\campaignion\CRM\Import\Field;

use Drupal\campaignion\CRM\Import\Source\SourceInterface;

class Address extends Field {

  public function __construct($field, $mapping) {
    foreach ($mapping as $target => $keys) {
      $mapping[$target] = is_array($keys) ? $keys : array($keys);
    }
    parent::__construct($field, $mapping);
  }

  public function getValue(SourceInterface $source) {
    $address = array();
    foreach ($this->source as $target => $keys) {
      $value = self::valueFromSource($source, $keys);
      if ($value) {
        $address[$target] = $value;
      }
    }
    if (empty($address)) {
      return FALSE;
    }
    if (!isset($address['country']) || empty($address['country'])) {
      $address['country'] = variable_get('site_default_country', 'AT');
    }
    return $address;
  }

  public function storeValue($entity, $new_address, $override) {
    return TRUE;
  }

  public function import(SourceInterface $source, $entity, $override) {
    try {
      if (!$override && $entity->{$this->field}->value()) {
        return FALSE;
      }
      if (($value = $this->getValue($source)) && ($value = $this->preprocessField($value))) {
        if ($this->storeValue($entity, $value, $override)) {
          $this->setValue($entity, $value);
          return TRUE;
        } else {
          return FALSE;
        }
      }
    } catch (\EntityMetadataWrapperException $e) {
      watchdog('ae_webform2redhen', 'Tried to import into a non-existing field "!field".', array('!field' => $this->field), WATCHDOG_WARNING);
    }
    return FALSE;
  }

  public function setValue($entity, $new_address) {
    $stored_multiple_addresses = $entity->{$this->field}->value();

    $changed = FALSE;
    foreach($stored_multiple_addresses as &$stored_address) {
      $found = TRUE;
      foreach ($new_address as $key => $value) {
        if (isset($stored_address[$key]) && $stored_address[$key] != $value) {
          $found = FALSE;
          break;
        }
      }
      if ($found) {
        // Do nothing if there is no new data in the new address.
        if (empty(array_diff($new_address, $stored_address))) {
          return FALSE;
        }
        $changed = TRUE;
        $stored_address = array_merge($stored_address, $new_address);
      }
    }
    if (!$changed) {
      $stored_multiple_addresses[] = $new_address;
    }
    $entity->{$this->field}->set($stored_multiple_addresses);
    return TRUE;
  }
}

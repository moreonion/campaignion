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
    $countryList = country_get_list();
    $empty_or_unknown_country = empty($address['country']) || !isset($countryList[$address['country']]);
    if ($empty_or_unknown_country && ($c = variable_get('site_default_country', 'AT'))) {
      $address['country'] = $c;
    }
    return $address;
  }

  public function storeValue($entity, $new_address) {
    return TRUE;
  }

  public function import(SourceInterface $source, \EntityMetadataWrapper $entity) {
    try {
      if (($value = $this->getValue($source)) && ($value = $this->preprocessField($value))) {
        if ($this->storeValue($entity, $value)) {
          return $this->setValue($entity, $value);
        } else {
          return FALSE;
        }
      }
    } catch (\EntityMetadataWrapperException $e) {
      watchdog('campaignion', 'Tried to import into a non-existing field "!field".', array('!field' => $this->field), WATCHDOG_WARNING);
    }
    return FALSE;
  }

  public function setValue(\EntityMetadataWrapper $entity, $new_address) {
    $stored_multiple_addresses = $entity->{$this->field}->value();

    // Trim all whitespace from start and end of input strings.
    foreach ($new_address as &$d) {
      $d = trim($d);
    }
    unset($d);

    $changed = FALSE;
    foreach($stored_multiple_addresses as &$stored_address) {
      $found = TRUE;
      // Check if the old address has any different non-NULL fields.
      // If not we have found a candidate for merging.
      foreach ($new_address as $key => $value) {
        if (isset($stored_address[$key]) && $stored_address[$key] != $value) {
          $found = FALSE;
          break;
        }
      }
      if ($found) {
        $diff = array_diff($new_address, $stored_address);
        if (empty($diff)) {
          // If there is no new data in $new_address then we are finished.
          return FALSE;
        }
        $changed = TRUE;
        $stored_address = array_merge($stored_address, $new_address);
      }
    }
    if (!$changed) {
      // We haven't found a matching existing address so we add this as new.
      $stored_multiple_addresses[] = $new_address;
    }
    $entity->{$this->field}->set($stored_multiple_addresses);
    // If we got here we've either added a new address or modified an old one.
    return TRUE;
  }
}

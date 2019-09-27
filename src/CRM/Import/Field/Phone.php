<?php

namespace Drupal\campaignion\CRM\Import\Field;

/**
 * Importer for phone numbers.
 */
class Phone extends Field {

  /**
   * Remove extra characters from a phone number for comparison.
   */
  protected function normalizePhoneNumber($number) {
    $number = trim($number);
    if (strpos($number, '+') === 0) {
      $number = '00' . substr($number, 1);
    }
    $number = preg_replace('/[^0-9]/', '', $number);
    return ltrim($number, '0');
  }

  /**
   * Determine whether two strings mean the same phone number.
   */
  protected function phoneNumbersEqual($short, $long) {
    $short = $this->normalizePhoneNumber($short);
    $long = $this->normalizePhoneNumber($long);
    if (strlen($short) > strlen($long)) {
      list($short, $long) = array($long, $short);
    }
    return substr($long, strlen($long) - strlen($short)) == $short;
  }

  /**
   * Determine whether a new value should be stored.
   */
  public function storeValue($entity, $new_number) {
    try {
      foreach ($entity->{$this->field}->value() as $delta => $stored_number) {
        if ($this->phoneNumbersEqual($stored_number, $new_number)) {
          return FALSE;
        }
      }
    }
    catch (\EntityMetadataWrapperException $e) {
      watchdog('campaignion', 'Searched data in a non-existing field "!field".', array('!field' => $this->field), WATCHDOG_WARNING);
      return TRUE;
    }
    // The number wasn't found.
    return TRUE;
  }

  /**
   * Update the field value.
   */
  public function setValue(\EntityMetadataWrapper $entity, $value) {
    $field = $entity->{$this->field};
    $values = $field->value();
    array_unshift($values, $value);
    $field->set($values);
    return TRUE;
  }

}

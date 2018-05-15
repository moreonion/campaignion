<?php

namespace Drupal\campaignion_newsletters;

/**
 * Convert newsletter values between form API and webform_submitted_data.
 */
class ValuePrefix {

  public static $noValueMap = [
    'checkbox' => [0 => 'no-change'],
    'radios' => [NULL => 'not-selected', 0 => 'not-selected'],
  ];

  /**
   * Convert form API values to stored values.
   *
   * @param mixed $value
   *   Form API value to convert.
   * @param string $prefix
   *   Prefix to add to the value.
   *
   * @return string
   *   Prefixed value for storing in webform_submitted_data.
   */
  public static function add($value, $prefix) {
    if (is_array($value)) {
      $value = reset($value);
    }
    if (isset(static::$noValueMap[$prefix][$value])) {
      $value = static::$noValueMap[$prefix][$value];
    }
    return $prefix . ':' . $value;
  }

  /**
   * Convert stored values to form API values.
   *
   * @param mixed $value
   *   Stored value to convert.
   *
   * @return string
   *   Un-prefixed value for form API.
   */
  public static function remove($value) {
    if (is_array($value)) {
      $value = reset($value);
    }
    $parts = explode(':', $value, 2);
    return count($parts) > 1 ? $parts[1] : $value;
  }

}

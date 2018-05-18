<?php

namespace Drupal\campaignion_opt_in;

/**
 * Namespace for form-value constants.
 */
class Values {

  const OPT_IN = 'opt-in';
  const OPT_OUT = 'opt-out';
  const NO_CHANGE = 'no-change';
  const NOT_SELECTED = 'not-selected';

  public static $noValueMap = [
    'checkbox' => [0 => self::NO_CHANGE],
    'checkbox-inverted' => [0 => self::OPT_IN],
    'radios' => [NULL => self::NOT_SELECTED, 0 => self::NOT_SELECTED],
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
  public static function addPrefix($value, $component) {
    if (is_array($value)) {
      $value = reset($value);
    }
    if (strpos($value, ':') !== FALSE) {
      // There is already a prefix.
      return $value;
    }

    $prefix = $component['extra']['display'];
    if ($prefix == 'checkbox' && !empty($component['extra']['invert_checkbox'])) {
      $prefix .= '-inverted';
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
  public static function removePrefix($value) {
    if (is_array($value)) {
      $value = reset($value);
    }
    $parts = explode(':', $value, 2);
    return count($parts) > 1 ? $parts[1] : $value;
  }

}

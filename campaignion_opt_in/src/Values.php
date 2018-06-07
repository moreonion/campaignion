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
    'checkbox' => [0 => [FALSE => self::NO_CHANGE, TRUE => self::OPT_OUT]],
    'checkbox-inverted' => [0 => self::OPT_IN],
    'radios' => [NULL => self::NOT_SELECTED, 0 => self::NOT_SELECTED],
  ];

  /**
   * Convert form API values to stored values.
   *
   * @param mixed $value
   *   Form API value to convert.
   * @param string $component
   *   The webform component.
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
    if (isset(static::$noValueMap[$prefix][$value])) {
      $value = static::$noValueMap[$prefix][$value];
      if (is_array($value)) {
        $value = $value[!empty($component['extra']['no_is_optout'])];
      }
    }
    return $prefix . ':' . $value;
  }

  /**
   * Split a value into its display and value part.
   *
   * @param mixed $value
   *   The value to split.
   *
   * @return string[]
   *   Array with two items:
   *   - The display or an emtpy string if there was none.
   *   - The value.
   */
  public static function split($value) {
    if (is_array($value)) {
      $value = reset($value);
    }
    $parts = explode(':', $value, 2);
    return count($parts) > 1 ? $parts : ['', $parts[0]];
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
    return static::split($value)[1];
  }

  /**
   * Return translated labels keyed by display and value.
   *
   * @return string[][]
   *   Translated labels.
   */
  protected static function labels() {
    return [
      'checkbox' => [
        Values::OPT_IN => t('Checkbox opt-in'),
        Values::NO_CHANGE => t('Checkbox no change'),
        Values::OPT_OUT => t('Checkbox opt-out'),
      ],
      'checkbox-inverted' => [
        Values::OPT_IN => t('Inverted checkbox opt-in'),
        Values::NO_CHANGE => t('Inverted checkbox no change'),
        Values::OPT_OUT => t('Inverted checkbox opt-out'),
      ],
      'radios' => [
        Values::OPT_IN => t('Radio opt-in'),
        Values::NO_CHANGE => t('Radio no change'),
        Values::OPT_OUT => t('Radio opt-out'),
        Values::NOT_SELECTED => t('Radio not selected (no change)'),
      ],
    ];
  }

  /**
   * Get label for values stored in submitted data.
   *
   * @param mixed $values
   *   Values as stored in the submitted data for this component.
   */
  public static function labelByValue($values) {
    if (!$values) {
      return t('Unknown value');
    }
    list($display, $value) = static::split($values);
    if ($value === '') {
      return t('Private or hidden by conditionals (no change)');
    }
    $labels = static::labels();
    if (isset($labels[$display][$value])) {
      return $labels[$display][$value];
    }
    return t('Unknown value');
  }

  /**
   * Get availabe options for a component.
   *
   * @param array $component
   *   The webform component.
   *
   * @return string[]
   *   Labels for available options keyed by the prefixed value.
   */
  public static function optionsByComponent(array $component) {
    $display = $component['extra']['display'];
    $labels = static::labels()[$display];
    if (!empty($component['extra']['no_is_optout'])) {
      unset($labels[Values::NO_CHANGE]);
    }
    else {
      unset($labels[Values::OPT_OUT]);
    }

    $prefixed_labels = [];
    foreach ($labels as $value => $label) {
      $prefixed_labels["$display:$value"] = $label;
    }
    return $prefixed_labels;
  }

}

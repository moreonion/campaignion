<?php

namespace Drupal\campaignion\CRM\Import\Field;

use Drupal\campaignion\CRM\Import\Source\SourceInterface;

/**
 * Importer for address fields.
 */
class Address extends Field {

  /**
   * Array of known countries keyed by their country codes.
   *
   * @var array
   *
   * @see country_get_list()
   */
  protected $countries;

  /**
   * Create a new address importer instance.
   */
  public function __construct($field, $mapping, $countries = NULL) {
    foreach ($mapping as $target => $keys) {
      $mapping[$target] = is_array($keys) ? $keys : array($keys);
    }
    parent::__construct($field, $mapping);
    $this->countries = is_null($countries) ? country_get_list() : $countries;
  }

  /**
   * Trim and normalize the source value.
   */
  protected static function valueFromSource(SourceInterface $source, $keys) {
    if ($value = parent::valueFromSource($source, $keys)) {
      return preg_replace('/\s+/', ' ', trim($value));
    }
  }

  public function getValue(SourceInterface $source) {
    $address = array();
    foreach ($this->source as $target => $keys) {
      $value = static::valueFromSource($source, $keys);
      if ($value) {
        $address[$target] = $value;
      }
    }
    if (empty($address)) {
      return FALSE;
    }
    $empty_or_unknown_country = empty($address['country']) || !isset($this->countries[$address['country']]);
    if ($empty_or_unknown_country && ($c = $this->defaultCountry($source))) {
      $address['country'] = $c;
    }
    return $address;
  }

  /**
   * Get the fallback country from the settings.
   */
  protected function defaultCountry(SourceInterface $source): ?string {
    $language = $source->getLanguageCode();
    // Explicitly set language specific default country.
    if (module_exists('i18n_variable') && ($country = i18n_variable_get('site_default_country', $language, ''))) {
      return $country;
    }
    // Default country for single language sites.
    if (!drupal_multilingual() && ($country = variable_get('site_default_country'))) {
      return $country;
    }
    // Language based country.
    if ($country = $this->countryFromLanguage($language)) {
      return $country;
    }
    // Non localized setting for multilingual sites.
    if ($country = variable_get_value('site_default_country')) {
      return $country;
    }
    return NULL;
  }

  /**
   * Get the country based on the language.
   *
   * We can’t use variable_get_value() here because Drupal sets an empty default
   * country on new installations. We want to ignore that by invoking the
   * variable “default callback” directly.
   */
  protected function countryFromLanguage($language) {
    $options['langcode'] = $language;
    return _campaignion_site_default_country_from_language(NULL, $options);
  }

  public function storeValue($entity, $new_address) {
    return TRUE;
  }

  public function setValue(\EntityMetadataWrapper $entity, $new_address) {
    $field = $entity->{$this->field};
    if ($field instanceof \EntityListWrapper) {
      return $this->setValueMultiple($field, $new_address);
    }
    else {
      return $this->setValueSingle($field, $new_address);
    }
  }

  /**
   * Merges a new address into an existing single-value address field.
   *
   * @param \EntityStructureWrapper $item
   *   The metadata wrapper for the single-value address field.
   * @param array $address
   *   Associative array representing the address to be merged.
   *
   * @return bool
   *   TRUE if any field value has been changed, FALSE if no changes were made.
   */
  protected function setValueSingle(\EntityStructureWrapper $item, array $address) {
    $stored = $item->value();
    if ($stored && $this->addressIsMergeable($stored, $address)) {
      if ($changed = $this->mergeAddress($stored, $address)) {
        $item->set($stored);
      }
      return $changed;
    }
    // Existing address contradicts the new one. So just set the new one.
    $item->set($address);
    return TRUE;
  }

  /**
   * Merges a new address into a multi-address field.
   *
   * @param \EntityListWrapper $list
   *   The metadata wrapper for the multi-value address field.
   * @param array $address
   *   Associative array representing the address to be merged.
   *
   * @return bool
   *   TRUE if any field value has been changed, FALSE if no changes were made.
   */
  protected function setValueMultiple(\EntityListWrapper $list, array $address) {
    $items = $list->value();
    $first = TRUE;
    foreach ($items as $delta => $item) {
      $item_array = $item instanceof \EntityStructureWrapper ? $item->value : $item;
      if ($this->addressIsMergeable($item_array, $address)) {
        if ($this->mergeAddress($item_array, $address) || !$first) {
          // Modified or confirmed addresses bubble to the top of the list.
          unset($items[$delta]);
          array_unshift($items, $item_array);
          $list->set(array_values($items));
          return TRUE;
        }
        // No new data and the matching address was already at the top.
        return FALSE;
      }
      $first = FALSE;
    }
    // We found no matching address so we add it as a new one.
    array_unshift($items, $address);
    $list->set(array_values($items));
    return TRUE;
  }

  /**
   * Check whether the second address can be merged into the first address item.
   *
   * @param array $a1
   *   The first address.
   * @param array $a2
   *   The scond address.
   *
   * @return bool
   *   TRUE when the first address contains no non-NULL values that differ from
   *   the second address.
   */
  protected function addressIsMergeable(array $a1, array $a2) {
    foreach ($a2 as $key => $value) {
      if (isset($a1[$key]) && $a1[$key] != $value) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Merges a new address into an existing address field item.
   *
   * @param array $item
   *   An address field item.
   * @param array $address
   *   Associative array representing the address to be merged.
   *
   * @return bool
   *   TRUE if any field value has been changed, FALSE if no changes were made.
   */
  protected function mergeAddress(array &$item, array $address) {
    if (!array_diff($address, $item)) {
      // New address doesn’t add new information. Nothing to do.
      return FALSE;
    }
    $item = array_merge($item, $address);
    return TRUE;
  }

}

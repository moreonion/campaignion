<?php

namespace Drupal\campaignion\CRM\Import\Field;

use \Drupal\campaignion\CRM\Import\Source\SourceInterface;

/**
 * Field importer for checkbox that should also import the unchecked state.
 */
class BooleanField extends Field {

  /**
   * {@inheritdoc}
   */
  public function import(SourceInterface $source, \EntityMetadataWrapper $entity) {
    try {
      $value = $this->preprocessField($this->getValue($source));
      if ($this->storeValue($entity, $value)) {
        return $this->setValue($entity, $value);
      } else {
        return FALSE;
      }
    } catch (\EntityMetadataWrapperException $e) {
      watchdog('campaignion_webform2redhen', 'Tried to import into a non-existing field "!field".', array('!field' => $this->field), WATCHDOG_WARNING);
    }
    return FALSE;
  }

  /**
   * Decide whether we have a new value for this field that warrants saving.
   */
  protected function storeValue($entity, $value) {
    return !is_null($value) && $entity->{$this->field}->value() !== $value;
  }

  /**
   * Use a checkbox to toggle a yes/no field.
   */
  protected static function valueFromSource(SourceInterface $source, $keys) {
    foreach ($keys as $key) {
      if ($source->hasKey($key)) {
        return $source->value($key) ? TRUE : FALSE;
      }
    }
  }

}

<?php

namespace Drupal\campaignion_mp_fields;

use \Drupal\campaignion_email_to_target\Api\Client;

/**
 * Extract UK postcodes from address fields and add MP data to an entity.
 */
class MPDataLoader {

  /**
   * Generate new instance from hard-coded configuration.
   *
   * @return static
   *   A new instance of this class.
   */
  public static function fromConfig() {
    $setters = [
      'mp_constituency' => function ($field, $constituency, $target) {
        if (!empty($constituency['name'])) {
          $field->set($constituency['name']);
        }
      },
      'mp_country' => function ($field, $constituency, $target) {
        if (!empty($constituency['country']['name'])) {
          $tagger = Tagger::byNameAndParentUuid('mp_country');
          $tagger->tagSingle($field, $constituency['country']['name'], TRUE);
        }
      },
      'mp_party' => function ($field, $constituency, $target) {
        if (!empty($target['political_affiliation'])) {
          $tagger = Tagger::byNameAndParentUuid('mp_party');
          $tagger->tagSingle($field, $target['political_affiliation'], TRUE);
        }
      },
      'mp_salutation' => function ($field, $constituency, $target) {
        if (!empty($target['salutation'])) {
          $field->set($target['salutation']);
        }
      },
    ];
    return new static($setters);
  }

  /**
   * Construct a new MPDataLoader.
   *
   * @param function[] $setters
   *   List of setter functions keyed by field names. Each function takes
   *   exactly 3 arguments:
   *   - field: The entity metadata wrapper representation of the field.
   *   - constituency: The constituency data from the API (or NULL).
   *   - target: The target data from the API (or NULL).
   *   The functions should set their field’s value if available.
   */
  public function __construct(array $setters) {
    $this->setters = $setters;
  }

  /**
   * Update the data in the MP fields.
   *
   * All addressfields on the entity are checked for a UK postcode. The first
   * postcode found is used to query the e2t database for data on the
   * constituency and MP.
   *
   * @param string $entity_type
   *   Type of the entity that is passed.
   * @param string $entity
   *   The entity that should have the data added.
   */
  public function setData($entity_type, $entity) {
    list($id, $rev_id, $bundle) = entity_extract_ids($entity_type, $entity);
    $fields = field_read_fields([
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'type' => 'addressfield',
    ]);
    if (!$fields) {
      return;
    }
    $target_fields = field_read_fields([
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'field_name' => array_keys($this->setters),
    ]);
    if (!$target_fields) {
      return;
    }
    $postcode = NULL;
    foreach ($fields as $field) {
      foreach (field_get_items($entity_type, $entity, $field['field_name']) as $item) {
        if ($item['postal_code'] && $item['country'] == 'GB') {
          $postcode = $item['postal_code'];
          break 2;
        }
      }
    }
    if ($postcode) {
      $api = Client::fromConfig();
      $data = $api->getTargets('mp', str_replace(' ', '', $postcode));
      if ($data) {
        $constituency = !empty($data[0]) ? $data[0] : NULL;
        $target = !empty($constituency['contacts'][0]) ? $constituency['contacts'][0] : NULL;
        $wrapped = entity_metadata_wrapper($entity_type, $entity);
        foreach ($target_fields as $field_name => $field) {
          $this->setters[$field_name]($wrapped->{$field_name}, $constituency, $target);
        }
      }
    }
  }

}

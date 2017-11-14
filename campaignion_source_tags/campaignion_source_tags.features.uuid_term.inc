<?php
/**
 * @file
 * campaignion_source_tags.features.uuid_term.inc
 */

/**
 * Implements hook_uuid_features_default_terms().
 */
function campaignion_source_tags_uuid_features_default_terms() {
  $terms = array();

  $terms[] = array(
    'name' => 'Source tags',
    'description' => 'Parent term for auto-tagging based on utm_source.',
    'format' => 'plain_text',
    'weight' => 0,
    'uuid' => '0fd2977e-9927-4de7-b4c2-e0bde71fc605',
    'language' => 'und',
    'i18n_tsid' => 0,
    'vocabulary_machine_name' => 'supporter_tags',
    'path' => array(
      'pathauto' => 1,
    ),
  );
  return $terms;
}

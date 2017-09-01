<?php

namespace Drupal\campaignion_mp_fields;

/**
 * Object that tags entities and is capable of adding tags as needed.
 */
class Tagger {

  protected static $instances = [];

  public static function byNameAndParentUUID($vocabulary_name, $parent_uuid = NULL, $reset = FALSE) {
    if (!isset(static::$instances[$vocabulary_name][$parent_uuid]) || $reset) {
      $ptid = 0;
      if ($parent_uuid) {
        $ids = entity_get_id_by_uuid('taxonomy_term', [$parent_uuid]);
        $ptid = reset($ids);
      }
      $vid = taxonomy_vocabulary_machine_name_load($vocabulary_name)->vid;
      static::$instances[$vocabulary_name][$parent_uuid] = new static($vid, $ptid);
    }
    return static::$instances[$vocabulary_name][$parent_uuid];
  }

  protected $map;
  protected $vid;
  protected $parent_tid;

  public function __construct($vid, $parent_tid) {
    $this->map = [];
    $sql = 'SELECT tid, name FROM {taxonomy_term_data} INNER JOIN {taxonomy_term_hierarchy} USING(tid) WHERE vid=:vid AND parent=:parent';
    $result = db_query($sql, [
      ':vid' => $vid,
      ':parent' => $parent_tid,
    ]);
    foreach ($result as $row) {
      $this->map[$row->name] = $row->tid;
    }
    $this->vid = $vid;
    $this->parent_tid = $parent_tid;
  }

  /**
   * Map tag to it’s tid and optionally create it if it doesn’t exist.
   */
  protected function mapTag($tag, $add) {
    if (!isset($this->map[$tag])) {
      if ($add) {
        $term = entity_create('taxonomy_term', ['name' => $tag, 'vid' => $this->vid, 'parent' => $this->parent_tid]);
        entity_save('taxonomy_term', $term);
        $this->map[$tag] = $term->tid;
      }
      else {
        return;
      }
    }
    return $this->map[$tag];
  }

  /**
   * Add tags to a contact.
   *
   * @param \EntityMetadataWrapper $field
   *   The contact to add the tags to.
   * @param string[] $tags
   *   List of tags to add.
   * @param bool $add
   *   Whether or not to create not (yet) existing tags.
   *
   * @return bool
   *   Whether the contact was changed or not.
   */
  public function tag(\EntityListWrapper $field, $tags, $add = FALSE) {
    $changed = FALSE;

    $items = [];
    foreach ($field->value() as $term) {
      $items[$term->tid] = $term->tid;
    }

    foreach ($tags as $tag) {
      if ($tid = $this->mapTag($tag, $add)) {
        if (!isset($items[$tid])) {
          $changed = TRUE;
          $items[$tid] = $tid;
        }
      }
    }

    if ($changed) {
      $field->set(array_keys($items));
    }
    return $changed;
  }

  /**
   * Tag single-tag field.
   */
  public function tagSingle(\EntityStructureWrapper $field, $tag, $add = FALSE) {
    $changed = FALSE;

    $tid = NULL;
    if ($term = $field->value()) {
      $tid = $term->tid;
    }

    if ($new_tid = $this->map[$tag]) {
      if ($new_tid != $tid) {
        $changed = TRUE;
        $field->set($new_tid);
      }
    }
    return $changed;
  }

}


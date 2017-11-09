<?php

namespace Drupal\campaignion_supporter_tags;

/**
 * Object that adds tags for specific vocabularies and parents.
 */
class Tagger {

  /**
   * Static cache for instances of this class.
   *
   * @var static[]
   */
  protected static $instances = [];

  /**
   * Create a contact tagger object from a vocabalury name and parent term uuid.
   *
   * @var string $vocabulary_name
   *   Name of the taxonomy vocabulary used for tagging.
   * @var string $parent_uuid
   *   UUID of the taxonomy term that should be the parent of all used tags.
   * @var boolean $reset
   *   Reset the static object cache.
   *
   * @return static
   *   An instance of this class.
   */
  public static function byNameAndParentUuid($vocabulary_name, $parent_uuid = NULL, $reset = FALSE) {
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

  /**
   * Associative array mapping taxonomy term names to their tids.
   *
   * @var int[]
   */
  protected $map;

  /**
   * The key of the vocabulary we are dealing with.
   *
   * @var int
   */
  protected $vid;

  /**
   * The tid of the parent taxonomy term if any.
   *
   * @var int
   */
  protected $parentTid;

  /**
   * Constructor.
   */
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
    $this->parentTid = $parent_tid;
  }

  /**
   * Add tags to a contact.
   *
   * @param array $field
   *   Taxonomy field items.
   * @param string[] $tags
   *   List of tags to add.
   * @param bool $add
   *   Whether or not to create not (yet) existing tags.
   *
   * @return bool
   *   Whether the contact was changed or not.
   */
  public function tag(&$field, array $tags, $add = FALSE) {
    $changed = FALSE;

    $items = [];
    if (!empty($field[LANGUAGE_NONE])) {
      foreach ($field[LANGUAGE_NONE] as $i) {
        $items[$i['tid']] = ['tid' => $i['tid']];
      }
    }

    foreach ($tags as $tag) {
      if (!isset($this->map[$tag])) {
        if ($add) {
          $term = entity_create('taxonomy_term', [
            'name' => $tag,
            'vid' => $this->vid,
            'parent' => $this->parentTid,
          ]);
          entity_save('taxonomy_term', $term);
          $this->map[$tag] = $term->tid;
        }
        else {
          continue;
        }
      }
      $tid = $this->map[$tag];
      if (!isset($items[$tid])) {
        $changed = TRUE;
        $items[$tid] = ['tid' => $tid];
      }
    }

    if ($changed) {
      $field[LANGUAGE_NONE] = $items;
    }
    return $changed;
  }

}

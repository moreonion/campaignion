<?php

namespace Drupal\campaignion_layout;

use Upal\DrupalUnitTestCase;

/**
 * Test whether menu-entries are manipulated as expected.
 *
 * NOTE: If you change anything in the theme alters you have to clear caches
 *       before the test results will change.
 */
class MenuTest extends DrupalUnitTestCase {

  const ENTITY_CALLBACK = 'campaignion_layout_get_theme_for_entity';
  const DUMMY_CALLBACK = 'campaignion_layout_no_custom_theme';

  /**
   * Create a test node.
   */
  public function setUp() {
    parent::setUp();
    $node = (object) ['type' => 'petition', 'title' => __CLASS__];
    node_object_prepare($node);
    node_save($node);
    $this->node = $node;
  }

  /**
   * Delete the test node.
   */
  public function tearDown() {
    node_delete($this->node->nid);
    parent::tearDown();
  }

  /**
   * Check that node paths get the right custom theme callback.
   */
  public function testNodePaths() {
    $item = menu_get_item("node/{$this->node->nid}/view");
    $this->assertEqual(self::ENTITY_CALLBACK, $item['theme_callback']);

    $item = menu_get_item("node/{$this->node->nid}");
    $this->assertEqual(self::ENTITY_CALLBACK, $item['theme_callback']);

    $item = menu_get_item("node/{$this->node->nid}/share");
    $this->assertEqual(self::ENTITY_CALLBACK, $item['theme_callback']);

    $item = menu_get_item("node/{$this->node->nid}/continue");
    $this->assertEqual(self::ENTITY_CALLBACK, $item['theme_callback']);

    $item = menu_get_item("node/{$this->node->nid}/edit");
    $this->assertEqual(self::DUMMY_CALLBACK, $item['theme_callback']);
  }

}

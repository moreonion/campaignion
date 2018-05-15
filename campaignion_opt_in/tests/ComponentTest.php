<?php

namespace Drupal\campaignion_opt_in;

/**
 * Test the webform component plugin.
 */
class ComponentTest extends \DrupalUnitTestCase {

  /**
   * Backup for $_GET values.
   *
   * @var array
   */
  protected $backupGet;

  /**
   * Backup global $_GET values.
   */
  public function setUp() {
    parent::setUp();
    $this->backupGet = $_GET;
    $_GET = ['q' => $_GET['q']];
  }

  /**
   * Restore global $_GET values.
   */
  public function tearDown() {
    $_GET = $this->backupGet;
    parent::tearDown();
  }

  /**
   * Test that the edit form works with default values.
   */
  public function testEditDefaults() {
    $node_stub = (object) ['nid' => 0, 'webform' => ['components' => []]];
    $_GET += [
      'name' => 'Opt-in Test',
      'required' => FALSE,
      'pid' => 0,
      'weight' => 0,
    ];
    $component = webform_menu_component_load('new', 0, 'opt_in');
    $form = drupal_get_form('webform_component_edit_form', $node_stub, $component);
    $this->assertEqual('Opt-in Test', $form['name']['#default_value']);
  }

  /**
   * Test that the render function works with the default values.
   */
  public function testRenderDefaults() {
    $component = webform_component_invoke('opt_in', 'defaults');
    $form = webform_component_invoke('opt_in', 'render', $component);
    $this->assertEqual('Opt-in', $form['#title']);
  }

}

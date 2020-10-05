<?php

namespace Drupal\campaignion_donation_amount;

use Upal\DrupalUnitTestCase;

/**
 * Test the webform component plugin.
 */
class ComponentTest extends DrupalUnitTestCase {

  /**
   * Backup for $_GET values.
   *
   * @var array
   */
  protected $backupGet;

  /**
   * Backup global $_GET values.
   */
  public function setUp() : void {
    parent::setUp();
    $this->backupGet = $_GET;
    $_GET = ['q' => $_GET['q']];
  }

  /**
   * Restore global $_GET values.
   */
  public function tearDown() : void {
    $_GET = $this->backupGet;
    parent::tearDown();
  }

  /**
   * Test that the edit form works with default values.
   */
  public function testEditDefaults() {
    $node_stub = (object) ['nid' => 0, 'webform' => ['components' => []]];
    $_GET += [
      'name' => 'Donation amount test',
      'required' => FALSE,
      'pid' => 0,
      'weight' => 0,
    ];
    $component = webform_menu_component_load('new', 0, 'donation_amount');
    $form = drupal_get_form('webform_component_edit_form', $node_stub, $component);
    $this->assertEqual('Donation amount test', $form['name']['#default_value']);
  }

  /**
   * Test that the render function works with the default values.
   */
  public function testRenderDefaults() {
    $component = webform_component_invoke('donation_amount', 'defaults');
    $form = webform_component_invoke('donation_amount', 'render', $component);
    $this->assertEqual('Donation amount', $form['#title']);
    $this->assertEqual('webform_number', $form['#type']);
  }

  /**
   * Test rendering with pre-defined values.
   */
  public function testRenderWithOptions() {
    $component = webform_component_invoke('donation_amount', 'defaults');
    $component['extra']['options'] = ['1', '2.00', '3'];
    $component['extra']['currency'] = 'EUR';

    $form = webform_component_invoke('donation_amount', 'render', $component);
    $this->assertEqual('select_or_other', $form['#type']);
    $this->assertEqual([
      '1' => '1',
      '2.00' => '2.00',
      '3' => '3',
    ], $form['#options']);
  }

}

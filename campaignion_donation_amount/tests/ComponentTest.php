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
    drupal_static_reset('form_set_error');
    $_SESSION['messages'] = [];
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

  /**
   * Test the number validation pass.
   */
  public function testValidate() {
    $component = webform_component_invoke('donation_amount', 'defaults');
    $element = webform_component_invoke('donation_amount', 'render', $component);
    $element['#value'] = "1\n2\n3";
    $element['#parents'] = ['amount'];
    $form_state['values']['extra'] = [
      'max' => '100',
      'min' => '',
      'step' => '',
      'integer' => FALSE,
      'decimals' => '',
    ];
    _webform_edit_donation_amount_validate_amounts($element, $form_state);
    $this->assertEmpty(drupal_static('form_set_error'));
    $this->assertEqual(['1', '2', '3'], $form_state['values']['amount']);
  }

  /**
   * Test the number validation with error.
   */
  public function testValidateError() {
    $component = webform_component_invoke('donation_amount', 'defaults');
    $element = webform_component_invoke('donation_amount', 'render', $component);
    $element['#value'] = "1\n2\n300";
    $element['#parents'] = ['amount'];
    $form_state['values']['extra'] = [
      'max' => '100',
      'min' => '',
      'step' => '',
      'integer' => FALSE,
      'decimals' => '',
    ];
    _webform_edit_donation_amount_validate_amounts($element, $form_state);
    $this->assertNotEmpty(drupal_static('form_set_error'));
  }

}

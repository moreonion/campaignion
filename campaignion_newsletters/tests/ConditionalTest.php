<?php

namespace Drupal\campaignion_newsletters;

/**
 * Test the webform conditionals integration.
 */
class ConditionalTest extends \DrupalUnitTestCase {

  /**
   * Include the component file.
   */
  public function setUp() {
    webform_component_include('newsletter');
  }

  /**
   * Test operator with values from the form-API radios.
   */
  public function testOperatorRadios() {
    $eq = '_webform_conditional_comparison_newsletter_equal';
    $ne = '_webform_conditional_comparison_newsletter_not_equal';

    // Possible input values:
    // - ['yes']: Yes radio is selected.
    // - ['no']: No radio is selected and no_is_optout is set.
    // - ['']: No radio is selected and no_is_optout is not set.
    // - []: No radio is selected.
    // Input value, rule value, component.
    $this->assertTrue($eq(['yes'], 'yes', []));
    $this->assertFalse($eq(['yes'], 'no', []));
    $this->assertFalse($eq(['yes'], '', []));
    $this->assertFalse($eq(['no'], 'yes', []));
    $this->assertTrue($eq(['no'], 'no', []));
    $this->assertFalse($eq(['no'], '', []));
    $this->assertFalse($eq([''], 'yes', []));
    $this->assertFalse($eq([''], 'no', []));
    $this->assertTrue($eq([''], '', []));
    $this->assertFalse($eq([], 'yes', []));
    $this->assertFalse($eq([], 'no', []));
    $this->assertTrue($eq([], '', []));

    $this->assertFalse($ne(['yes'], 'yes', []));
    $this->assertTrue($ne(['yes'], 'no', []));
    $this->assertTrue($ne(['yes'], '', []));
    $this->assertTrue($ne(['no'], 'yes', []));
    $this->assertFalse($ne(['no'], 'no', []));
    $this->assertTrue($ne(['no'], '', []));
    $this->assertTrue($ne([''], 'yes', []));
    $this->assertTrue($ne([''], 'no', []));
    $this->assertFalse($ne([''], '', []));
    $this->assertTrue($ne([], 'yes', []));
    $this->assertTrue($ne([], 'no', []));
    $this->assertFalse($ne([], '', []));
  }

  /**
   * Test operator with values from the form-API checkbox.
   */
  public function testOperatorCheckbox() {
    $eq = '_webform_conditional_comparison_newsletter_equal';
    $ne = '_webform_conditional_comparison_newsletter_not_equal';

    $this->assertTrue($eq(['subscribed' => 'subscribed'], 'yes', []));
    $this->assertFalse($eq(['subscribed' => 'subscribed'], 'no', []));
    $this->assertFalse($eq(['subscribed' => 'subscribed'], '', []));
    $this->assertFalse($eq(['subscribed' => 0], 'yes', []));
    $this->assertFalse($eq(['subscribed' => 0], 'no', []));
    $this->assertTrue($eq(['subscribed' => 0], '', []));

    $this->assertFalse($ne(['subscribed' => 'subscribed'], 'yes', []));
    $this->assertTrue($ne(['subscribed' => 'subscribed'], 'no', []));
    $this->assertTrue($ne(['subscribed' => 'subscribed'], '', []));
    $this->assertTrue($ne(['subscribed' => 0], 'yes', []));
    $this->assertTrue($ne(['subscribed' => 0], 'no', []));
    $this->assertFalse($ne(['subscribed' => 0], '', []));
  }

  /**
   * Test operator with stored values.
   */
  public function testOperatorStoredValues() {
    $eq = '_webform_conditional_comparison_newsletter_equal';
    $ne = '_webform_conditional_comparison_newsletter_not_equal';

    $this->assertTrue($eq(['subscribed'], 'yes', []));
    $this->assertFalse($eq(['subscribed'], 'no', []));
    $this->assertFalse($eq(['subscribed'], '', []));
    $this->assertFalse($eq(['unsubscribed'], 'yes', []));
    $this->assertTrue($eq(['unsubscribed'], 'no', []));
    $this->assertFalse($eq(['unsubscribed'], '', []));
    $this->assertFalse($eq([''], 'yes', []));
    $this->assertFalse($eq([''], 'no', []));
    $this->assertTrue($eq([''], '', []));

    $this->assertFalse($ne(['subscribed'], 'yes', []));
    $this->assertTrue($ne(['subscribed'], 'no', []));
    $this->assertTrue($ne(['subscribed'], '', []));
    $this->assertTrue($ne(['unsubscribed'], 'yes', []));
    $this->assertFalse($ne(['unsubscribed'], 'no', []));
    $this->assertTrue($ne(['unsubscribed'], '', []));
    $this->assertTrue($ne([''], 'yes', []));
    $this->assertTrue($ne([''], 'no', []));
    $this->assertFalse($ne([''], '', []));
  }

}

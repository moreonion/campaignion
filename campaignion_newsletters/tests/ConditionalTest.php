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

    $this->assertTrue($eq(['yes'], 'yes', []));
    $this->assertFalse($eq(['yes'], 'no', []));
    $this->assertFalse($eq(['no'], 'yes', []));
    $this->assertTrue($eq(['no'], 'no', []));
    $this->assertFalse($eq([], 'yes', []));
    $this->assertTrue($eq([], 'no', []));

    $this->assertFalse($ne(['yes'], 'yes', []));
    $this->assertTrue($ne(['yes'], 'no', []));
    $this->assertTrue($ne(['no'], 'yes', []));
    $this->assertFalse($ne(['no'], 'no', []));
    $this->assertTrue($ne([], 'yes', []));
    $this->assertFalse($ne([], 'no', []));
  }

  /**
   * Test operator with values from the form-API checkbox.
   */
  public function testOperatorCheckbox() {
    $eq = '_webform_conditional_comparison_newsletter_equal';
    $ne = '_webform_conditional_comparison_newsletter_not_equal';

    $this->assertTrue($eq(['subscribed' => 'subscribed'], 'yes', []));
    $this->assertFalse($eq(['subscribed' => 'subscribed'], 'no', []));
    $this->assertFalse($eq(['subscribed' => 0], 'yes', []));
    $this->assertTrue($eq(['subscribed' => 0], 'no', []));

    $this->assertFalse($ne(['subscribed' => 'subscribed'], 'yes', []));
    $this->assertTrue($ne(['subscribed' => 'subscribed'], 'no', []));
    $this->assertTrue($ne(['subscribed' => 0], 'yes', []));
    $this->assertFalse($ne(['subscribed' => 0], 'no', []));
  }

  /**
   * Test operator with stored values.
   */
  public function testOperatorStoredValues() {
    $eq = '_webform_conditional_comparison_newsletter_equal';
    $ne = '_webform_conditional_comparison_newsletter_not_equal';

    $this->assertTrue($eq(['subscribed'], 'yes', []));
    $this->assertFalse($eq(['subscribed'], 'no', []));
    $this->assertFalse($eq([''], 'yes', []));
    $this->assertTrue($eq([''], 'no', []));

    $this->assertFalse($ne(['subscribed'], 'yes', []));
    $this->assertTrue($ne(['subscribed'], 'no', []));
    $this->assertTrue($ne([''], 'yes', []));
    $this->assertFalse($ne([''], 'no', []));
  }

}

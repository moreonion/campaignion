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
    // - ['no change']: No radio is selected and no_is_optout is not set.
    // - []: No radio is selected.
    // Input value, rule value, component.
    $this->assertTrue($eq(['yes'], 'yes', []));
    $this->assertFalse($eq(['yes'], 'no', []));
    $this->assertFalse($eq(['yes'], 'no change', []));
    $this->assertFalse($eq(['yes'], 'no selection', []));
    $this->assertFalse($eq(['no'], 'yes', []));
    $this->assertTrue($eq(['no'], 'no', []));
    $this->assertFalse($eq(['no'], 'no change', []));
    $this->assertFalse($eq(['no change'], 'yes', []));
    $this->assertFalse($eq(['no change'], 'no', []));
    $this->assertTrue($eq(['no change'], 'no change', []));
    $this->assertFalse($eq(['no change'], 'no selection', []));
    $this->assertFalse($eq([], 'yes', []));
    $this->assertFalse($eq([], 'no', []));
    $this->assertFalse($eq([], 'no change', []));
    $this->assertTrue($eq([], 'no selection', []));

    $this->assertFalse($ne(['yes'], 'yes', []));
    $this->assertTrue($ne(['yes'], 'no', []));
    $this->assertTrue($ne(['yes'], 'no change', []));
    $this->assertTrue($ne(['yes'], 'no selection', []));
    $this->assertTrue($ne(['no'], 'yes', []));
    $this->assertFalse($ne(['no'], 'no', []));
    $this->assertTrue($ne(['no'], 'no change', []));
    $this->assertTrue($ne(['no change'], 'yes', []));
    $this->assertTrue($ne(['no change'], 'no', []));
    $this->assertFalse($ne(['no change'], 'no change', []));
    $this->assertTrue($ne(['no change'], 'no selection', []));
    $this->assertTrue($ne([], 'yes', []));
    $this->assertTrue($ne([], 'no', []));
    $this->assertTrue($ne([], 'no change', []));
    $this->assertFalse($ne([], 'no selection', []));
  }

  /**
   * Test operator with values from the form-API checkbox.
   */
  public function testOperatorCheckbox() {
    $eq = '_webform_conditional_comparison_newsletter_equal';
    $ne = '_webform_conditional_comparison_newsletter_not_equal';

    $this->assertTrue($eq(['subscribed' => 'subscribed'], 'yes', []));
    $this->assertFalse($eq(['subscribed' => 'subscribed'], 'no', []));
    $this->assertFalse($eq(['subscribed' => 'subscribed'], 'no change', []));
    $this->assertFalse($eq(['subscribed' => 'subscribed'], 'no selection', []));
    $this->assertFalse($eq(['subscribed' => 0], 'yes', []));
    $this->assertFalse($eq(['subscribed' => 0], 'no', []));
    $this->assertTrue($eq(['subscribed' => 0], 'no change', []));
    $this->assertFalse($eq(['subscribed' => 0], 'no selection', []));

    $this->assertFalse($ne(['subscribed' => 'subscribed'], 'yes', []));
    $this->assertTrue($ne(['subscribed' => 'subscribed'], 'no', []));
    $this->assertTrue($ne(['subscribed' => 'subscribed'], 'no change', []));
    $this->assertTrue($ne(['subscribed' => 0], 'yes', []));
    $this->assertTrue($ne(['subscribed' => 0], 'no', []));
    $this->assertFalse($ne(['subscribed' => 0], 'no change', []));
    $this->assertTrue($ne(['subscribed' => 0], 'no selection', []));
  }

  /**
   * Test operator with stored values.
   */
  public function testOperatorStoredValues() {
    $eq = '_webform_conditional_comparison_newsletter_equal';
    $ne = '_webform_conditional_comparison_newsletter_not_equal';

    $this->assertTrue($eq(['subscribed'], 'yes', []));
    $this->assertFalse($eq(['subscribed'], 'no', []));
    $this->assertFalse($eq(['subscribed'], 'no change', []));
    $this->assertFalse($eq(['subscribed'], 'no selection', []));
    $this->assertFalse($eq(['unsubscribed'], 'yes', []));
    $this->assertTrue($eq(['unsubscribed'], 'no', []));
    $this->assertFalse($eq(['unsubscribed'], 'no change', []));
    $this->assertFalse($eq(['unsubscribed'], 'no selection', []));
    $this->assertFalse($eq(['no change'], 'yes', []));
    $this->assertFalse($eq(['no change'], 'no', []));
    $this->assertTrue($eq(['no change'], 'no change', []));
    $this->assertFalse($eq(['no change'], 'no selection', []));
    $this->assertFalse($eq(['no selection'], 'yes', []));
    $this->assertFalse($eq(['no selection'], 'no', []));
    $this->assertFalse($eq(['no selection'], 'no change', []));
    $this->assertTrue($eq(['no selection'], 'no selection', []));

    $this->assertFalse($ne(['subscribed'], 'yes', []));
    $this->assertTrue($ne(['subscribed'], 'no', []));
    $this->assertTrue($ne(['subscribed'], 'no change', []));
    $this->assertTrue($ne(['subscribed'], 'no selection', []));
    $this->assertTrue($ne(['unsubscribed'], 'yes', []));
    $this->assertFalse($ne(['unsubscribed'], 'no', []));
    $this->assertTrue($ne(['unsubscribed'], 'no change', []));
    $this->assertTrue($ne(['unsubscribed'], 'no selection', []));
    $this->assertTrue($ne(['no change'], 'yes', []));
    $this->assertTrue($ne(['no change'], 'no', []));
    $this->assertFalse($ne(['no change'], 'no change', []));
    $this->assertTrue($ne(['no change'], 'no selection', []));
    $this->assertTrue($ne(['no selection'], 'yes', []));
    $this->assertTrue($ne(['no selection'], 'no', []));
    $this->assertTrue($ne(['no selection'], 'no change', []));
    $this->assertFalse($ne(['no selection'], 'no selection', []));
  }

}

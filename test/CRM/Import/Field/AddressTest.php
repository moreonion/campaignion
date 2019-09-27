<?php

namespace Drupal\campaignion\CRM\Import\Field;

require_once dirname(__FILE__) . '/RedhenEntityTest.php';
use Drupal\campaignion\CRM\Import\Source\ArraySource;

class AddressTest extends RedhenEntityTest {

  static protected $mapping = array(
    'thoroughfare' => 'street_address',
    'postal_code' => 'zip_code',
    'locality' => 'state',
    'country' => 'country',
  );

  static protected $testdata_at = array(
    'street_address' => 'Hütteldorferstraße 253',
    'zip_code' => '1140',
    'state' => 'Wien',
    'country' => 'AT',
  );

  /**
   * Map an array of data from form keys to address fields format.
   *
   * @param string[] $data
   *   Address with form keys.
   *
   * @return string[]
   *   Address field item.
   */
  protected static function mapped(array $data) {
    $mapped = array();
    foreach (self::$mapping as $field_key => $data_key) {
      if (isset($data[$data_key])) {
        $mapped[$field_key] = $data[$data_key];
      }
    }
    if (!isset($mapped['country']) && ($c = variable_get('site_default_country', ''))) {
      $mapped['country'] = $c;
    }
    return $mapped;
  }

  /**
   * Extract some keys from an array.
   *
   * @param array $data
   *   An associative array.
   * @param array $keys
   *   Keys that should be extracted.
   *
   * @return array
   *   An array with all keys from $data that are also in $keys.
   */
  protected static function filtered(array $data, array $keys) {
    return array_intersect_key($data, array_flip($keys));
  }

  /**
   * Set up test data.
   */
  public function setUp() {
    parent::setUp();
    $this->importer = new Address('field_address', self::$mapping);
    $this->contact = $this->newRedhenContact();
    $this->fakeContact = $this->createMock('EntityMetadataWrapper');
    $this->fakeContact->field_address = $this->contact->field_address[0];
  }

  /**
   * Shortcut for importing data into the test contact.
   */
  protected function import($data) {
    return $this->importer->import(new ArraySource($data), $this->fakeContact);
  }
  
  /**
   * Test importing a full address.
   */
  public function testWithAllFields() {
    $data = self::$testdata_at;
    $this->import($data);
    $this->assertEqual([$this->mapped($data)], $this->contact->field_address->value());
  }

  /**
   * Test importing only the country field.
   */
  public function testWithOnlyCountry() {
    $data = self::filtered(self::$testdata_at, ['country']);
    $this->import($data);
    $this->assertEqual([$this->mapped($data)], $this->contact->field_address->value());
  }

  /**
   * Test importing only the locality.
   */
  public function testWithOnlyLocality() {
    $data = self::filtered(self::$testdata_at, ['street_address']);
    $this->import($data);
    $this->assertEqual([$this->mapped($data)], $this->contact->field_address->value());
  }

  /**
   * Test return value for identical imports.
   */
  function testTwoIndenticalImports_returnValueFalse() {
    $this->assertTrue($this->import(self::$testdata_at), 'Import into new contact returned FALSE intead of TRUE.');
    $this->assertFalse($this->import(self::$testdata_at), 'Import of identical datat returned TRUE twice.');
    $data = self::filtered(self::$testdata_at, ['street_address']);
    $this->assertFalse($this->import($data), 'Import of identical street_address/throughfare returned TRUE instead of FALSE.');
  }

  /**
   * Test single-value field with full address.
   */
  public function testSingleFullAddress() {
    // Import full address.
    $this->assertTrue($this->import(self::$testdata_at));
    $expected = $this->mapped(self::$testdata_at);
    $this->assertEqual($expected, $this->contact->field_address->value()[0]);

    // Setting again should not change anything.
    $this->assertFalse($this->import(self::$testdata_at));
  }

  /**
   * Test adding new data to existing address.
   */
  public function testSingleChangeAddress() {
    // Import only country.
    $data = self::filtered(self::$testdata_at, ['country']);
    $expected = $this->mapped($data);
    $this->assertTrue($this->import($data));
    $this->assertEqual($expected, $this->contact->field_address->value()[0]);

    // Add rest of the address data.
    $expected = $this->mapped(self::$testdata_at);
    $this->assertTrue($this->import(self::$testdata_at));
    $this->assertEqual($expected, $this->contact->field_address->value()[0]);
  }

  /**
   * Test importing address with multiple spaces.
   */
  public function testImportMultipleSpaces() {
    $data['street_address'] = 'Multiple  spaces ';
    $expected = $this->mapped(['street_address' => 'Multiple spaces']);
    $this->assertTrue($this->import($data));
    $this->assertEqual($expected, $this->contact->field_address->value()[0]);
  }

}


<?php

namespace Drupal\campaignion\CRM\Import\Field;

require_once dirname(__FILE__) . '/RedhenEntityTest.php';
use \Drupal\campaignion\CRM\Import\Source\ArraySource;

class AddressTest extends RedhenEntityTest {
  static protected $mapping = array(
    'thoroughfare' => 'street_address',
    'postal_code' => 'zip_code',
    'locality' => 'state',
    'country' => 'country',
  );

  static protected $testdata = array(
    'street_address' => 'Hütteldorferstraße 253',
    'zip_code' => '1140',
    'state' => 'Wien',
    'country' => 'AT',
  );

  static protected function mapped(&$data) {
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

  static protected function filteredData($keys) {
    return array_intersect_key(self::$testdata, array_flip($keys));
  }
  
  function testWithAllFields() {
    $importer = new Address('field_address', self::$mapping);
    $entity = $this->newRedhenContact();
    $importer->import(new ArraySource(self::$testdata), $entity);
    $this->assertEqual(array($this->mapped(self::$testdata)), $entity->field_address->value());
  }

  function testWithOnlyCountry() {
    $importer = new Address('field_address',  self::$mapping);
    $entity = $this->newRedhenContact();
    $data = self::filteredData(array('country'));
    $importer->import(new ArraySource($data), $entity);
    $this->assertEqual(array($this->mapped($data)), $entity->field_address->value());
  }

  function testWithOnlyLocality() {
    $importer = new Address('field_address', self::$mapping);
    $entity = $this->newRedhenContact();
    $data = self::filteredData(array('street_address'));
    $importer->import(new ArraySource($data), $entity);
    $this->assertEqual(array($this->mapped($data)), $entity->field_address->value());
  }

  function testTwoIndenticalImports_returnValueFalse() {
    $importer = new Address('field_address', self::$mapping);
    $entity = $this->newRedhenContact();
    $this->assertTrue($importer->import(new ArraySource(self::$testdata), $entity), 'Import into new contact returned FALSE intead of TRUE.');
    $this->assertFalse($importer->import(new ArraySource(self::$testdata), $entity), 'Import of identical datat returned TRUE twice.');
    $data = self::filteredData(array('street_address'));
    $this->assertFalse($importer->import(new ArraySource($data), $entity), 'Import of identical street_address/throughfare returned TRUE instead of FALSE.');
  }

}


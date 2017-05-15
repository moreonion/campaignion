<?php

namespace Drupal\campaignion\CRM\Import\Field;
require_once dirname(__FILE__) . '/RedhenEntityTest.php';

use \Drupal\campaignion\CRM\Import\Source\ArraySource;

class FieldTest extends RedhenEntityTest {
  function testSimpleStringNoErrors() {
    $field = 'first_name'; $string = 'Somename';
    $importer = new Field($field);
    $data[$field] = $string;
    $entity = $this->newRedhenContact();
    $importer->import(new ArraySource($data), $entity, TRUE);
    $this->assertEqual($string, $entity->first_name->value());
  }

  function testCallPreprocess(){
    $field = 'first_name'; $string = 'Somename';
    $importer = $this->getMockBuilder(Field::class)
      ->setMethods(['preprocessField'])->setConstructorArgs([$field])
      ->getMock();
    $data[$field] = $string;
    $entity = $this->newRedhenContact();
    $importer->expects($this->once())->method('preprocessField')->with($this->identicalTo($data[$field]));
    $importer->import(new ArraySource($data), $entity, TRUE);
  }

  function testNotExistingFieldIsIgnored() {
    $field = 'nofield_with_this_name'; $string = 'Somename';
    $importer = new Field($field);
    $data[$field] = $string;
    $entity = $this->newRedhenContact();
    $importer->import(new ArraySource($data), $entity, TRUE);
  }

  function testNonEntityGivesPHPError() {
    $field = 'first_name'; $string = 'Somename';
    $importer = new Field($field);
    $data[$field] = $string;
    $entity = NULL;
    $this->expectException(class_exists('TypeError') ? 'TypeError' : 'PHPUnit_Framework_Error');
    $this->assertEqual(NULL, $importer->import(new ArraySource($data), $entity, TRUE));
  }
}

<?php

namespace Drupal\campaignion_action\Redirects;

/**
 * Test for the redirect model class.
 */
class RedirectTest extends \DrupalUnitTestCase {

  /**
   * Create a simple instance and test the array export.
   */
  public function testToArray() {
    $t = new Redirect([
      'label' => 'Test redirect',
      'destination' => 'node/50',
    ]);
    $m = $t->toArray();
    unset($m['prettyDestination']);
    $this->assertEqual([
      'id' => NULL,
      'label' => 'Test redirect',
      'destination' => 'node/50',
      'filters' => [],
    ], $m);
  }

  /**
   * Test constructing an instance from an array.
   */
  public function testConstructFromArray() {
    $data = [
      'label' => 'Test redirect',
      'destination' => 'node/50',
      'filters' => [],
    ];
    $t = new Redirect($data);
    $this->assertEqual('node/50', $t->destination);
  }

  /**
   * Test cloning of message templates.
   */
  public function testRedirectCloning() {
    $data = [
      'id' => 42,
      'label' => 'Test label',
      'destination' => 'node/50',
      'filters' => [
        [
          'id' => 42,
          'type' => 'test',
          'value' => 1,
        ],
      ],
    ];
    $t1 = new Redirect($data);
    $t2 = clone $t1;

    // Test that the cloned message counts as being new.
    $this->assertNull($t2->id);
    $this->assertTrue($t2->isNew());

    // Test that filters have been cloned too.
    $this->assertTrue($t2->filters[0]->isNew());
    $t2->filters[0]->config['value'] = 2;
    $this->assertEqual(1, $t1->filters[0]->config['value']);
  }

}

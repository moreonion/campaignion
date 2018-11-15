<?php

namespace Drupal\campaignion_email_to_target\SelectionMode;

use Drupal\campaignion_email_to_target\Message;

/**
 * Test the “One” selection plugin.
 */
class OneTest extends \DrupalUnitTestcase {

  /**
   * Test run with two targets/messages.
   */
  public function testTwoTargets() {
    $mode = new One(TRUE);
    $pairs = [
      [['id' => 'target1'], new Message([])],
      [['id' => 'target2'], new Message([])],
    ];
    $element = $mode->formElement($pairs);
    $this->assertNotEmpty($element['selector']);
    $this->assertCount(2, $element['selector']['#options']);
    $this->assertEqual('selector', element_children($element, TRUE)[0]);

    $values = $mode->getValues($element, [
      'selector' => 'target2',
      'target1' => [],
      'target2' => [],
    ]);
    // Only one of the values can be selected.
    $this->assertCount(1, $values);
  }

}

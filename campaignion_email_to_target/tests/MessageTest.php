<?php

namespace Drupal\campaignion_email_to_target;

/**
 * Test the message objects.
 */
class MessageTest extends \DrupalUnitTestCase {

  /**
   * Test replacing a non-constituency nested value.
   */
  public function testReplaceTokensWithNestedValues() {
    $target = ['trust' => ['country' => 'Wales']];
    $message = new Message(['message' => '[email-to-target:trust.country]']);
    $message->replaceTokens($target);
    $this->assertEqual('Wales', $message->message);
  }

  /**
   * Test replacing a constituency nested value.
   */
  public function testReplaceTokensWithConstituencyValues() {
    $target = ['constituency' => ['country' => 'Wales']];
    $message = new Message(['message' => '[email-to-target:constituency.country]']);
    $message->replaceTokens($target);
    $this->assertEqual('Wales', $message->message);
  }

}

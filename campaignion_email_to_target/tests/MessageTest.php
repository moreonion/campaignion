<?php

namespace Drupal\campaignion_email_to_target;

/**
 * Test the message objects.
 */
class MessageTest extends \DrupalUnitTestCase {

  public function testReplaceTokensWithNestedValues() {
    $target = ['trust' => ['country' => 'Wales']];
    $message = new Message(['message' => '[email-to-target:trust.country]']);
    $message->replaceTokens($target);
    $this->assertEqual('Wales', $message->message);
  }

}

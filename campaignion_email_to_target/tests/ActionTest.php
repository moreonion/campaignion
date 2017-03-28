<?php

namespace Drupal\campaignion_email_to_target;

use \Drupal\campaignion_action\TypeBase;
use \Drupal\campaignion_email_to_target\Api\Client;
use \Drupal\little_helpers\Webform\Submission;

class ActionTest extends \DrupalUnitTestCase {

  /**
   * New Action with all methods mocked that would need database access.
   */
  protected function mockAction($targets) {
    $api = $this->getMockBuilder(Client::class)
      ->disableOriginalConstructor()
      ->getMock();
    $api->method('getTargets')->will($this->returnValue($targets));
    $node = (object) ['nid' => 47114711];
    $type = new TypeBase('test');
    $action = $this->getMockBuilder(Action::class)
      ->setConstructorArgs([$type, $node, $api])
      ->setMethods(['getOptions', 'getExclusion', 'getMessage'])
      ->getMock();
    $action->method('getOptions')->will($this->returnValue([
      'dataset_name' => 'mp',
    ]));
    $submission_o = $this->getMockBuilder(Submission::class)
      ->disableOriginalConstructor()
      ->getMock();
    return [$action, $api, $submission_o];
  }

  /**
   * Create a message with the replaceTokens() method mocked.
   */
  protected function createMessage($data) {
    return $this->getMockBuilder(Message::class)
      ->setConstructorArgs([$data])
      ->setMethods(['replaceTokens'])
      ->getMock();
  }

  /**
   * Test targetMessagePairs() with messages and all types of exclusions.
   */
  public function testTargetMessagePairs() {
    $contacts = [
      ['first_name' => 'Alice'],
      ['first_name' => 'Bob'],
      ['first_name' => 'Claire'],
    ];
    list($action, $api, $submission_o) = $this->mockAction([
      [
        'name' => 'Constituency 1',
        'contacts' => $contacts,
      ],
      [
        'name' => 'Excluded',
        'contacts' => [
          ['first_name' => 'David'],
        ]
      ],
    ]);
    $m = $this->createMessage([
      'type' => 'message',
      'label' => 'Default message',
      'subject' => 'Default subject',
      'header' => 'Default header',
      'message' => 'Default message',
      'footer' => 'Default footer',
    ]);
    $e = $this->createMessage([
      'type' => 'exclusion',
      'message' => 'excluded first!',
    ]);
    $action->method('getMessage')->will($this->returnCallback(function ($t, $c) use ($e, $m) {
      if ($t['first_name'] == 'Bob') {
        return $e;
      }
      return $m;
    }));
    $self = $this;
    $action->method('getExclusion')->will($this->returnCallback(function ($c) use ($self) {
      if ($c['name'] == 'Excluded') {
        return $self->createMessage([
          'type' => 'exclusion',
          'message' => 'excluded!',
        ]);
      }
    }));
    list($pairs, $no_target_element) = $action->TargetMessagePairs($submission_o);
    $this->assertEqual([[$contacts[0], $m], [$contacts[2], $m]], $pairs);
    $this->assertEqual(['#markup' => "<p>excluded first!</p>\n"], $no_target_element);
  }

}


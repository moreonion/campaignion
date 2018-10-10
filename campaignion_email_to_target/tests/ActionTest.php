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
    $api->method('getDataset')->will($this->returnValue((object) [
      'dataset_name' => 'test_dataset',
      'selectors' => [['title' => 'test_selector', 'filters' => []]],
    ]));
    $node = (object) ['nid' => 47114711];
    $type = new TypeBase('test');
    $action = $this->getMockBuilder(Action::class)
      ->setConstructorArgs([$type, $node, $api])
      ->setMethods(['getOptions', 'getExclusion', 'getMessage'])
      ->getMock();
    $action->method('getOptions')->will($this->returnValue([
      'dataset_name' => 'test_dataset',
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
    $c1 = ['name' => 'Constituency 1'];
    $contacts = [
      ['first_name' => 'Alice', 'constituency' => $c1],
      ['first_name' => 'Bob', 'constituency' => $c1],
      ['first_name' => 'Claire', 'constituency' => $c1],
      ['first_name' => 'David', 'constituency' => ['name' => 'Excluded']],
    ];
    list($action, $api, $submission_o) = $this->mockAction($contacts);
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
    $action->method('getMessage')->will($this->returnCallback(function ($t) use ($e, $m) {
      if ($t['first_name'] == 'Bob') {
        return $e;
      }
      return $m;
    }));
    $self = $this;
    $action->method('getExclusion')->will($this->returnCallback(function ($t) use ($self) {
      if ($t['constituency']['name'] == 'Excluded') {
        return $self->createMessage([
          'type' => 'exclusion',
          'message' => 'excluded!',
        ]);
      }
    }));
    list($pairs, $no_target_element) = $action->targetMessagePairs($submission_o);
    $this->assertEqual([[$contacts[0], $m], [$contacts[2], $m]], $pairs);
    $this->assertEqual(['#markup' => "<p>excluded first!</p>\n"], $no_target_element);
  }

}


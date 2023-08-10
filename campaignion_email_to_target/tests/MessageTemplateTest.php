<?php

namespace Drupal\campaignion_email_to_target;


class MessageTemplateTest extends \DrupalUnitTestCase {

  public function test_toArray() {
    $t = new MessageTemplate([
      'subject' => 'Test Subject',
      'label' => 'Test label',
    ]);
    $this->assertEqual([
      'id' => NULL,
      'type' => 'message',
      'label' => 'Test label',
      'filters' => [],
      'subject' => 'Test Subject',
      'header' => '',
      'message' => '',
      'footer' => '',
      'url' => NULL,
      'urlLabel' => NULL,
    ], $t->toArray());
  }

  public function test_construct_fromArray() {
    $data = [
      'type' => 'message',
      'label' => 'Test label',
      'filters' => [],
      'subject' => 'Test Subject',
      'header' => '',
      'message' => '',
      'footer' => '',
      'urlLabel' => NULL,
    ];
    $t = new MessageTemplate($data);
    $this->assertEqual('Test Subject', $t->subject);
  }

  /**
   * Test cloning of message templates.
   */
  public function testMessageCloning() {
    $data = [
      'id' => 42,
      'type' => 'message',
      'label' => 'Test label',
      'filters' => [[
        'id' => 42,
        'type' => 'test',
        'config' => ['value' => 1],
      ]],
      'subject' => 'Test Subject',
      'header' => '',
      'message' => '',
      'footer' => '',
    ];
    $t1 = new MessageTemplate($data);
    $t2 = clone $t1;

    // Test that the cloned message counts as being new.
    $this->assertNull($t2->id);
    $this->assertTrue($t2->isNew());

    // Test that filters have been cloned too.
    $this->assertTrue($t2->filters[0]->isNew());
    $t2->filters[0]->config['value'] = 2;
    $this->assertEqual(1, $t1->filters[0]->config['value']);
  }

  /**
   * Test copying a filter from one message template to another.
   */
  public function testCopyFilter() {
    $data = [
      'id' => 42,
      'type' => 'message',
      'label' => 'A',
      'filters' => [[
        'type' => 'test',
        'config' => ['value' => 1],
      ]],
    ];
    $template_a = new MessageTemplate($data);
    $template_a->filters[0]->id = 42;

    $data = [
      'id' => 43,
      'type' => 'message',
      'label' => 'B',
      'filters' => [],
    ];
    $template_b = new MessageTemplate($data);

    // Copy as array.
    $template_b->setFilters([$template_a->filters[0]->toArray()]);
    $this->assertEquals(42, $template_a->filters[0]->id);
    $this->assertNotEquals(42, $template_b->filters[0]->id);

    // Copy as filter object.
    $template_b->setFilters([$template_a->filters[0]]);
    $this->assertEquals(42, $template_a->filters[0]->id);
    $this->assertNotEquals(42, $template_b->filters[0]->id);
  }

  /**
   * Test creating a message instance.
   */
  public function testCreateMessageFromInstance() {
    // The type used in the default messages.
    $t = new MessageTemplate([
      'type' => 'message',
      'message' => 'non-default',
    ]);
    $m = $t->createInstance();
    $this->assertInstanceOf(Message::class, $m);
    $this->assertEquals('non-default', $m->message);

    // This is type used in the vue-app.
    $t = new MessageTemplate([
      'type' => 'message-template',
      'message' => 'non-default',
    ]);
    $m = $t->createInstance();
    $this->assertInstanceOf(Message::class, $m);
    $this->assertEquals('non-default', $m->message);
    $t = new MessageTemplate([
      'type' => 'exclusion',
      'message' => 'non-default',
      'url' => 'redirect',
    ]);

    $m = $t->createInstance();
    $this->assertInstanceOf(Exclusion::class, $m);
    $this->assertEquals('non-default', $m->message);
    $this->assertEquals('redirect', $m->url);
  }

}

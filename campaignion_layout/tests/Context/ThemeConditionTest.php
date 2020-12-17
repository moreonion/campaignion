<?php

namespace Drupal\campaignion_layout\Context;

use Drupal\campaignion_layout\Tests\ThemesBaseTest;

/**
 * Test the theme context condition implementation.
 */
class ThemeConditionTest extends ThemesBaseTest {

  /**
   * Test the available form options for the context condition plugin.
   */
  public function testFormOptions() {
    $this->injectThemes([
      'foo' => ['title' => 'Foo'],
      'bar' => ['title' => 'Bar'],
    ]);
    $condition = new ThemeCondition('plugin', []);
    $this->assertEqual([
      'foo' => 'Foo',
      'bar' => 'Bar',
    ], $condition->condition_values());
  }

  /**
   * Test the behaviour of the execute function.
   */
  public function testExecuteWithMultipleContexts() {
    $mock_condition = $this->getMockBuilder(ThemeCondition::class)
      ->disableOriginalConstructor()
      ->setMethods(['condition_met', 'get_contexts'])
      ->getMock();
    $theme = 'theme_name';
    $matching_contexts = ['one', 'two', 'three'];
    $mock_condition->expects($this->once())
      ->method('get_contexts')
      ->with('theme_name')
      ->willReturn($matching_contexts);
    $mock_condition->expects($this->exactly(3))
      ->method('condition_met')
      ->withConsecutive(
        ['one', $theme],
        ['two', $theme],
        ['three', $theme]
      );
    $mock_condition->execute($theme);
  }

  /**
   * Test the behaviour of the execute function.
   */
  public function testExecuteWithNoContexts() {
    $mock_condition = $this->getMockBuilder(ThemeCondition::class)
      ->disableOriginalConstructor()
      ->setMethods(['condition_met', 'get_contexts'])
      ->getMock();
    $theme = 'theme_name';
    $matching_contexts = [];
    $mock_condition->expects($this->once())
      ->method('get_contexts')
      ->with('theme_name')
      ->willReturn($matching_contexts);
    $mock_condition->expects($this->exactly(0))
      ->method('condition_met');
    $mock_condition->execute($theme);
  }

}

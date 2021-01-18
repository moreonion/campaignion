<?php

namespace Drupal\campaignion_layout;

use Upal\DrupalUnitTestCase;

/**
 * Test for the theme data wrapper.
 */
class ThemeTest extends DrupalUnitTestCase {

  /**
   * Test checking for the layout_variations feature.
   */
  public function testHasFeature() {
    $data = (object) ['info' => ['features' => ['foo', 'bar']]];
    $theme = new Theme($data, $this->createMock(Themes::class));
    $this->assertFalse($theme->hasFeature());

    $data = (object) [
      'info' => ['features' => ['foo', 'layout_variations', 'baz']],
    ];
    $theme = new Theme($data, $this->createMock(Themes::class));
    $this->assertTrue($theme->hasFeature());
  }

  /**
   * Test checking for layouts being enabled.
   */
  public function testIsEnabled() {
    $mock_builder = $this->getMockBuilder(Theme::class)
      ->setMethods(['setting']);

    $theme = $mock_builder->setConstructorArgs([
      (object) ['status' => 0],
      $this->createMock(Themes::class),
    ])->getMock();
    $this->assertFalse($theme->isEnabled());

    $theme = $mock_builder->setConstructorArgs([
      (object) ['status' => 1],
      $this->createMock(Themes::class),
    ])->getMock();
    $this->assertTrue($theme->isEnabled());
  }

  /**
   * Test checking whether the layout feature is declared and enabled.
   */
  public function testHasFeatureEnabled() {
    $mock_builder = $this->getMockBuilder(Theme::class)
      ->setMethods(['setting']);

    $theme = $mock_builder->setConstructorArgs([
      (object) [],
      $this->createMock(Themes::class),
    ])->getMock();
    $theme->method('setting')->will($this->onConsecutiveCalls(FALSE, TRUE));
    $this->assertFalse($theme->hasFeature());
    $this->assertFalse($theme->hasFeatureEnabled());
    $this->assertFalse($theme->hasFeatureEnabled());

    $theme = $mock_builder->setConstructorArgs([
      (object) ['info' => ['features' => ['layout_variations']]],
      $this->createMock(Themes::class),
    ])->getMock();
    $theme->method('setting')->will($this->onConsecutiveCalls(FALSE, TRUE));
    $this->assertTrue($theme->hasFeature());
    $this->assertFalse($theme->hasFeatureEnabled());
    $this->assertTrue($theme->hasFeatureEnabled());
  }

  /**
   * Test getting the default layout from the theme or its base theme.
   */
  public function testDefaultLayout() {
    // No declared default layout and no base theme.
    $data = (object) ['info' => []];
    $theme_without_default = new Theme($data, $this->createMock(Themes::class));
    $this->assertEqual('default', $theme_without_default->defaultLayout());

    // Declared default layout.
    $data = (object) ['info' => ['layout_default' => 'foo']];
    $theme_foo_layout = new Theme($data, $this->createMock(Themes::class));
    $this->assertEqual('foo', $theme_foo_layout->defaultLayout());

    // No declared default layout but a base theme with a default layout.
    $data = (object) ['info' => []];
    $theme_without_default = new Theme($data, $this->createMock(Themes::class), $theme_foo_layout);
    $this->assertEqual('foo', $theme_without_default->defaultLayout());

    // Declared default layout wins over default layout of the base theme.
    $data = (object) ['info' => ['layout_default' => 'bar']];
    $theme_bar_layout = new Theme($data, $this->createMock(Themes::class), $theme_foo_layout);
    $this->assertEqual('bar', $theme_bar_layout->defaultLayout());
  }

  /**
   * Test getting all enabled layouts as options.
   */
  public function testLayoutOptions() {
    $mock_builder = $this->getMockBuilder(Theme::class)
      ->setMethods(['setting']);
    $mock_themes = $this->createMock(Themes::class);
    $mock_themes->method('declaredLayouts')->willReturn([
      'foo' => ['name' => 'foo', 'title' => 'Foo', 'fields' => []],
      'bar' => ['name' => 'bar', 'title' => 'Bar', 'fields' => []],
      'baz' => ['name' => 'baz', 'title' => 'Baz', 'fields' => []],
    ]);

    $theme = $mock_builder->setConstructorArgs([
      (object) [
        'status' => 1,
        'name' => 'foo',
        'info' => [
          'layout' => ['foo', 'baz'],
        ],
      ],
      $mock_themes,
    ])->getMock();
    // No setting yet → No layouts activated.
    $this->assertEqual([], $theme->layoutOptions());

    // Activate the bar-layout and baz-layout although bar is not implemented.
    $theme->method('setting')->willReturn([
      'foo' => 0,
      'bar' => 'bar',
      'baz' => 'baz',
    ]);
    $this->assertEqual([
      'baz' => 'Baz',
    ], $theme->layoutOptions());

    // List all implemented layouts.
    $this->assertEqual([
      'foo' => ['name' => 'foo', 'title' => 'Foo', 'fields' => []],
      'baz' => ['name' => 'baz', 'title' => 'Baz', 'fields' => []],
    ], $theme->layouts(TRUE));

    // Test child theme inheritance.
    $child_theme = $mock_builder->setConstructorArgs([
      (object) [
        'status' => 1,
        'name' => 'foo',
        'info' => [
          'layout' => ['bar'],
        ],
      ],
      $mock_themes,
      $theme,
    ])->getMock();
    // List all implemented layouts in the child theme.
    $this->assertEqual([
      'foo' => ['name' => 'foo', 'title' => 'Foo', 'fields' => []],
      'bar' => ['name' => 'bar', 'title' => 'Bar', 'fields' => []],
      'baz' => ['name' => 'baz', 'title' => 'Baz', 'fields' => []],
    ], $child_theme->layouts(TRUE));
  }

}

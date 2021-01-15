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
      ->setMethods(['hasFeature', 'setting']);

    $theme = $mock_builder->setConstructorArgs([
      (object) ['status' => 0],
      $this->createMock(Themes::class),
    ])->getMock();
    $this->assertFalse($theme->isEnabled());

    $theme = $mock_builder->setConstructorArgs([
      (object) ['status' => 1],
      $this->createMock(Themes::class),
    ])->getMock();
    $this->assertFalse($theme->isEnabled());

    $theme->method('hasFeature')->willReturn(TRUE);
    $this->assertFalse($theme->isEnabled());

    $theme->method('setting')->willReturn(TRUE);
    $this->assertTrue($theme->isEnabled());
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

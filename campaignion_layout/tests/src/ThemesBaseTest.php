<?php

namespace Drupal\campaignion_layout\Tests;

use Drupal\little_helpers\Services\Container;
use Upal\DrupalUnitTestCase;

use Drupal\campaignion_layout\Theme;
use Drupal\campaignion_layout\Themes;

/**
 * Base test for testing theme & layout handling.
 *
 * The test class provides a simple way of injecting a Themes service with
 * themes and layouts configured.
 */
abstract class ThemesBaseTest extends DrupalUnitTestCase {

  /**
   * Cleanup the injected service.
   */
  public function tearDown() : void {
    Container::get()->inject('campaignion_layout.themes', NULL);
    parent::tearDown();
  }

  /**
   * Inject a themes service with specific theme and layout data.
   */
  protected function injectThemes(array $themes = [], array $layouts = []) {
    $theme_objects = [];
    $add_layout_defaults = function ($info) {
      return $info + ['fields' => []];
    };
    foreach ($themes as $name => $data) {
      $data += ['layouts' => []];
      $theme = $this->getMockBuilder(Theme::class)
        ->disableOriginalConstructor()
        ->setMethods(['title', 'layouts'])
        ->getMock();
      $theme->method('title')->willReturn($data['title'] ?? $name);
      $theme->method('layouts')
        ->willReturn(array_map($add_layout_defaults, $data['layouts']));
      $theme_objects[$name] = $theme;
      $layouts += $data['layouts'];
    }
    $themes = $this->getMockBuilder(Themes::class)
      ->disableOriginalConstructor()
      ->setMethods(['enabledThemes', 'declaredLayouts'])
      ->getMock();
    $themes->method('enabledThemes')->willReturn($theme_objects);
    $themes->method('declaredLayouts')
      ->willReturn(array_map($add_layout_defaults, $layouts));
    Container::get()->inject('campaignion_layout.themes', $themes);
  }

}

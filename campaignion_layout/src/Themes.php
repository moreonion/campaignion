<?php

namespace Drupal\campaignion_layout;

/**
 * Service that interacts with our custom theme-API.
 *
 * Themes that want to provide layouts must have the 'layout_variations' feature
 * activated. If the toggle is active the information about the provided layout
 * variations is queried by invoking THEME_campaignion_layout_info().
 *
 * The module automatically renders an additional configuration on the theme
 * settings page that allows to selectively enable or disable layouts.
 */
class Themes {

  protected $themes;

  public static function fromConfig() {
    return new static(list_themes());
  }

  public function __construct(array $themes) {
    $this->themes = $themes;
  }

  /**
   * Get an #options-array with all enabled themes with layout variations.
   *
   * @return string[]
   *   Theme names keyed by theme machine names.
   */
  public function themeOptions() {
    $enabled_themes = array_filter($this->themes, function ($theme) {
      return $theme->status && theme_get_setting('toggle_layout_variations', $theme->name);
    });
    return array_map(function ($theme) {
      return $theme->info['name'];
    }, $enabled_themes);
  }

  /**
   * Get enabled layout variations for a theme as a #options-array.
   *
   * @param string $theme_name
   *   The machine name of the theme.
   * @param boolean $disabled
   *   Whether to include disabled variations.
   *
   * @return string[]
   *   Machine name as key mapped to the translated title for each enabled
   *   layout variation.
   */
  public function layoutOptions(string $theme_name, bool $disabled = FALSE) {
    return array_map(function (array $info) {
      return $info['title'];
    }, $this->layouts($theme_name, $disabled));
  }

  /**
   * Get info about all enabled layout variations for a theme.
   */
  public function layouts(string $theme_name, bool $disabled = FALSE) {
    $variations = $this->invokeLayoutHook($this->themes[$theme_name]);
    if (!$disabled) {
      $enabled = theme_get_setting('layout_variations', $theme_name);
      // If not explicitly set (NULL) we assume all variations are enabled.
      if (!is_null($enabled)) {
        $variations = array_intersect_key($variations, array_filter($enabled));
      }
    }
    return $variations;
  }

  /**
   * Include the theme’s template.php and invoke its hook.
   */
  protected function invokeLayoutHook($theme) {
    $this->loadThemeFunctions($theme);
    $func = $theme->name . '_campaignion_layout_info';
    return function_exists($func) ? $func() : [];
  }

  /**
   * Helper function to include a theme’s template.php.
   *
   * @see _drupal_theme_initialize().
   */
  protected function loadThemeFunctions($theme) {
    // Find all our ancestor themes and put them in an array.
    $base_theme = array();
    $ancestor = $theme->name;
    while ($ancestor && isset($this->themes[$ancestor]->base_theme)) {
      $ancestor = $this->themes[$ancestor]->base_theme;
      $base_theme[] = $this->themes[$ancestor];
    }
    $base_theme = array_reverse($base_theme);

    // Initialize the theme.
    if (isset($theme->engine)) {
      // Include the engine.
      include_once DRUPAL_ROOT . '/' . $theme->owner;

      $theme_engine = $theme->engine;
      if (function_exists($theme_engine . '_init')) {
        foreach ($base_theme as $base) {
          call_user_func($theme_engine . '_init', $base);
        }
        call_user_func($theme_engine . '_init', $theme);
      }
    }
    else {
      // include non-engine theme files
      foreach ($base_theme as $base) {
        // Include the theme file or the engine.
        if (!empty($base->owner)) {
          include_once DRUPAL_ROOT . '/' . $base->owner;
        }
      }
      // and our theme gets one too.
      if (!empty($theme->owner)) {
        include_once DRUPAL_ROOT . '/' . $theme->owner;
      }
    }
  }


}


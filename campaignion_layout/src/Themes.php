<?php

namespace Drupal\campaignion_layout;

/**
 * Service for instantiating theme classes.
 */
class Themes {

  /**
   * Theme data for all available themes.
   *
   * @var object[]
   *
   * @see list_themes()
   */
  protected $themes;

  /**
   * The cache bin to use for caching declared layouts.
   *
   * @var \DrupalCacheInterface
   */
  protected $cache;

  /**
   * Create a new instance by reading the theme data from list_themes().
   */
  public static function fromConfig() {
    return new static(list_themes(), _cache_get_object('cache'));
  }

  /**
   * Create a new instance by passing the theme data.
   */
  public function __construct(array $themes, \DrupalCacheInterface $cache) {
    $this->themes = $themes;
    $this->cache = $cache;
  }

  /**
   * Create instance for a single theme.
   */
  public function getTheme($theme_name) {
    if ($theme = $this->themes[$theme_name] ?? NULL) {
      $base = isset($theme->base_theme) ? $this->getTheme($theme->base_theme) : NULL;
      return new Theme($theme, $this, $base);
    }
  }

  /**
   * Get all enabled themes.
   */
  public function enabledThemes() {
    $self = $this;
    $all_themes = array_map(function ($theme) use ($self) {
      return $self->getTheme($theme->name);
    }, $this->themes);
    return array_filter($all_themes, function ($theme) {
      return $theme->isEnabled();
    });
  }

  /**
   * Get all declared layouts.
   */
  public function declaredLayouts() {
    $cid = self::class . '::' . __FUNCTION__;
    $info = &drupal_static($cid);
    if (isset($info)) {
      return $info;
    }
    if ($cache = $this->cache->get($cid)) {
      return $info = $cache->data;
    }
    $info = [];
    foreach ($this->enabledThemes() as $theme) {
      $info = drupal_array_merge_deep($info, $theme->invokeLayoutHook());
    }
    foreach ($info as $name => &$i) {
      $i += ['name' => $name, 'fields' => []];
      foreach ($i['fields'] as $field_name => &$f) {
        $f += [
          'display' => [],
          'variable' => $field_name,
        ];
      }
    }
    $this->cache->set($cid, $info);
    return $info;
  }

}

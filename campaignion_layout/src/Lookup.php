<?php

namespace Drupal\campaignion_layout;

use Drupal\little_helpers\Services\Container;

/**
 * Class that determines which theme or layout to use based on an entity.
 */
class Lookup {

  /**
   * The entity which’s field we are looking at.
   *
   * @var \Drupal\campaignion_layout\Entity
   */
  protected $entity;

  /**
   * Instance of the themes service.
   *
   * @var \Drupal\campaignion_layout\Themes
   */
  protected $themes;

  /**
   * Greate a new instance by passing the entity.
   */
  public static function fromEntity(string $entity_type, $entity) {
    $themes = Container::get()->loadService('campaignion_layout.themes');
    return new static($themes, new Entity($entity_type, $entity));
  }

  /**
   * Create a new instance by passing themes and entty.
   */
  public function __construct(Themes $themes, Entity $entity) {
    $this->themes = $themes;
    $this->entity = $entity;
  }

  /**
   * Iterate over layout_selection field items.
   */
  public function iterateItems() {
    foreach ($this->entity->fieldsOfType('layout_selection') as $field_name) {
      foreach ($this->entity->getItems($field_name) ?: [] as $item) {
        yield $item;
      }
    }
  }

  /**
   * Find the first item with an enabled theme.
   *
   * @return string|null
   *   The machine name of the first configured theme that’s available (if any).
   */
  public function getTheme() {
    foreach ($this->iterateItems() as $item) {
      if (($theme = $this->themes->getTheme($item['theme'])) && $theme->hasFeatureEnabled()) {
        return $item['theme'];
      }
    }
  }

  /**
   * Find the layout configured for the currently active theme.
   */
  public function getLayout() {
    return $this->themes->getTheme()->getLayoutFromItems($this->iterateItems());
  }

  /**
   * Get the order for the form for the currently active layout.
   *
   * @return string|null
   *   The machine name of the ordering.
   */
  public function getOrder() {
    $layout = $this->getLayout();
    foreach ($this->iterateItems() as $item) {
      if (($order = $item['order']) && $layout['name'] === $item['layout'] && $layout['order_enabled']) {
        return $order;
      }
    }
  }

}

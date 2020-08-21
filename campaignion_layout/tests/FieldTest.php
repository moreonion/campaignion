<?php

namespace Drupal\campaignion_layout;

use Drupal\little_helpers\Services\Container;
use Upal\DrupalUnitTestCase;

/**
 * Test for the field integration.
 */
class FieldTest extends DrupalUnitTestCase {

  /**
   * Clean up the injected services.
   */
  public function tearDown() {
    Container::get()->inject('campaignion_layout.themes', NULL);
    parent::tearDown();
  }

  /**
   * Test rendering the field widget if no themes are available.
   */
  public function testFieldWidgetWithoutThemes() {
    $themes = $this->createMock(Themes::class);
    $themes->method('enabledThemes')->willReturn([]);
    Container::get()->inject('campaignion_layout.themes', $themes);
    $form = [];
    $form_state = [];
    $element = campaignion_layout_field_widget_form($form, $form_state, NULL, NULL, NULL, [], 0, []);
    $this->assertEqual([], $element['theme']['#options']);
    $this->assertFalse($element['#access']);
  }

  /**
   * Test rendering the field widget with themes.
   */
  public function testFieldWidgetWithThemes() {
    $theme_a = $this->createMock(Theme::class);
    $theme_a->method('title')->willReturn('Theme A');
    $theme_a->method('layouts')->willReturn([
      '2col' => ['title' => 'Two columns', 'fields' => []],
      'banner' => ['title' => 'Banner', 'fields' => ['banner' => TRUE]],
    ]);
    $theme_b = $this->createMock(Theme::class);
    $theme_b->method('title')->willReturn('Theme B');
    $theme_b->method('layouts')->willReturn([
      '1col' => ['title' => 'Single column', 'fields' => []],
    ]);
    $themes = $this->createMock(Themes::class);
    $themes->method('enabledThemes')->willReturn([
      'a' => $theme_a,
      'b' => $theme_b,
    ]);
    Container::get()->inject('campaignion_layout.themes', $themes);
    $form = [];
    $form_state = [];
    $element = campaignion_layout_field_widget_form($form, $form_state, NULL, NULL, NULL, [], 0, []);
    $this->assertEqual([
      'a' => 'Theme A',
      'b' => 'Theme B',
    ], $element['theme']['#options']);
    $this->assertNotEmpty($element['layout_a']['#options']);
    $this->assertEqual([
      '' => 'Default layout',
      '2col' => 'Two columns',
      'banner' => 'Banner',
    ], $element['layout_a']['#options']);
    $this->assertNotEmpty($element['layout_b']['#options']);
    $this->assertEqual([
      'banner' => ['#layout-a input' => ['banner']],
    ], $form_state['campaignion_layout_fields']);

    $element['theme']['#value'] = 'a';
    $element['layout_a']['#value'] = 'banner';
    $element['layout_b']['#value'] = '1col';
    $element['layout']['#parents'] = ['layout'];
    $form_state['values'] = [];
    _campaignion_layout_field_widget_validate($element, $form_state, $form);
    $this->assertEqual('banner', $form_state['values']['layout']);
  }

  /**
   * Test field item empty check.
   */
  public function testIsEmpty() {
    $item = ['theme' => '', 'layout' => 'foo'];
    $this->assertTrue(campaignion_layout_field_is_empty($item, NULL));
    $item = ['theme' => 'bar', 'layout' => 'foo'];
    $this->assertFalse(campaignion_layout_field_is_empty($item, NULL));
  }

  /**
   * Test that #states are added to the node form.
   */
  public function testNodeFormAlter() {
    $form['layout_background_image'] = [];
    $form_state = [];
    campaignion_layout_form_node_form_alter($form, $form_state);
    $this->assertFalse($form['layout_background_image']['#access']);
    unset($form['layout_background_image']['#access']);

    $form_state['campaignion_layout_fields']['layout_background_image']['#layout-a input'] = ['banner'];
    campaignion_layout_form_node_form_alter($form, $form_state);
    $expected['visible']['#layout-a input']['value'] = 'banner';
    $this->assertEqual($expected, $form['layout_background_image']['#states']);
  }

}

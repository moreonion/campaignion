<?php

/**
 * @file
 * Hook implementations for the our custom field type and field widget.
 */

use Drupal\campaignion_layout\Themes;
use Drupal\little_helpers\ArrayConfig;

/**
 * Implements hook_field_info().
 */
function campaignion_layout_field_info() {
  $info['layout_selection'] = [
    'label' => t('Layout selection'),
    'description' => t('This field stores a theme and layout selection.'),
    'default_widget' => 'layout_selector',
  ];
  return $info;
}

/**
 * Implements hook_field_widget_info().
 */
function campaignion_layout_field_widget_info() {
  $info['layout_selector'] = [
    'label' => t('Theme and layout selector'),
    'field types' => ['layout_selection'],
    'behaviors' => [
      'multiple values' => FIELD_BEHAVIOR_DEFAULT,
      'default value' => FIELD_BEHAVIOR_DEFAULT,
    ],
  ];
  return $info;
}

/**
 * Implements hook_field_widget_form().
 */
function campaignion_layout_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
  $element += [
    '#type' => 'fieldset',
    '#element_validate' => ['_campaignion_layout_field_widget_validate'],
  ];
  $item = $items[$delta] ?? [];
  ArrayConfig::mergeDefaults($item, [
    'theme' => NULL,
    'layout' => '',
  ]);

  $themes = Themes::fromConfig();
  $theme_options = $themes->themeOptions();
  $theme_id = drupal_html_id('campaignion-layout-theme');
  $element['theme'] = [
    '#type' => 'select',
    '#title' => t('Theme'),
    '#empty_value' => NULL,
    '#empty_option' => t('- Default -'),
    '#description' => t('This theme is used when displaying the content.'),
    '#options' => $theme_options,
    '#default_value' => $item['theme'],
    '#id' => $theme_id,
  ];
  $element['layout'] = [
    '#type' => 'value',
    '#value' => $item['layout'],
  ];
  foreach ($theme_options as $theme_name => $title) {
    $options = ['' => t('Default layout')];
    $html_id = drupal_html_id("layout_{$theme_name}");
    foreach ($themes->layouts($theme_name) as $layout => $info) {
      $options[$layout] = $info['title'];
      foreach (array_keys(array_filter($info['fields'])) as $field_name) {
        $fields[$field_name]["#$html_id input"][] = $layout;
      }
    }
    $states['visible']["#{$theme_id}"]['value'] = $theme_name;
    $element["layout_{$theme_name}"] = [
      '#type' => 'radios',
      '#title' => t('Layout'),
      '#description' => t('This layout is used for displaying the content.'),
      '#options' => $options,
      '#default_value' => $item['layout'],
      '#states' => $states,
      '#id' => $html_id,
    ];
  }
  $form_state['campaignion_layout_fields'] = $fields;
  return $element;
}

/**
 * Validation callback for the field widget.
 */
function _campaignion_layout_field_widget_validate($element, &$form_state, $form) {
  $theme_name = $element['theme']['#value'];
  $layout = $theme_name ? $element["layout_{$theme_name}"]['#value'] : NULL;
  form_set_value($element['layout'], $layout, $form_state);
}

/**
 * Implements hook_field_is_empty().
 */
function campaignion_layout_field_is_empty($item, $field) {
  return empty($item['theme']);
}

<?php

namespace Drupal\campaignion_newsletters;

use Drupal\campaignion_opt_in\FormBuilderElementOptIn;
use Drupal\form_builder_webform\Form;
use Drupal\form_builder\Loader;

/**
 * Test the component behaviour.
 *
 * This includes test for the component callbacks defined in
 * campaignion_newsletter.component.inc.
 */
class WebformComponentTest extends \DrupalUnitTestCase {

  /**
   * Load the components include file.
   */
  public function setUp() {
    parent::setUp();
    require_once drupal_get_path('module', 'webform') . '/includes/webform.components.inc';
  }

  /**
   *
   */
  public function testFormBuilderConfigure() {
    $loader = Loader::instance();
    $form = $loader->getForm('webform', 1, NULL);
    $element['#webform_component'] = [
      'type' => 'opt_in',
      'form_key' => 'newsletter',
      'extra' => ['channel' => 'email'],
    ];
    $element['#weight'] = 0;
    $element['#form_builder'] = [];
    webform_component_defaults($element['#webform_component']);
    $e = $loader->getElement('webform', 1, 'opt_in', $form, $element);
    $this->assertInstanceOf(FormBuilderElementOptIn::class, $e);
    $form_state = form_state_defaults();
    $config_form = $e->configurationForm([], $form_state);
    $this->assertArrayHasKey('lists', $config_form);
  }

}

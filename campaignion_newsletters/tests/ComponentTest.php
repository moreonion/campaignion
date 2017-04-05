<?php

namespace Drupal\campaignion_newsletters;

class ComponentTest extends \DrupalUnitTestCase {

  public function setUp() {
    require_once drupal_get_path('module', 'campaignion_newsletters') . '/campaignion_newsletters.component.inc';
  }

  public function testSubmitCheckbox() {
    $c['extra']['display'] = 'checkbox';

    // Not checked checkbox.
    $v['subscribed'] = 0;
    $this->assertEqual([''], _webform_submit_newsletter($c, $v));

    // Checked checkbox.
    $v['subscribed'] = 'subscribed';
    $this->assertEqual(['subscribed'], _webform_submit_newsletter($c, $v));
  }

  public function testSubmitRadios() {
    $c['extra']['display'] = 'radios';

    // Not checked checkbox.
    $v = 'no';
    $this->assertEqual([''], _webform_submit_newsletter($c, $v));

    // Checked checkbox.
    $v = 'yes';
    $this->assertEqual(['subscribed'], _webform_submit_newsletter($c, $v));
  }

}

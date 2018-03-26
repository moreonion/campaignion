<?php

namespace Drupal\campaignion_newsletters;

class ComponentTest extends \DrupalUnitTestCase {

  public function setUp() {
    require_once drupal_get_path('module', 'campaignion_newsletters') . '/campaignion_newsletters.component.inc';
  }

  public function testComponentDefaults() {
    $defaults = webform_component_invoke('newsletter', 'defaults');

    $expected_subset = array(
      'extra' => array(
        'opt_in_implied' => 1,
        'send_welcome' => 0,
        'optin_statement' => '',
      )
    );

    $this->assertArraySubset($expected_subset, $defaults);

    $component = webform_component_invoke('newsletter', 'edit', $defaults);

    $this->assertEqual('textarea', $component['extra']['optin_statement']['#type']);
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

    // Radio no.
    $v = 'no';
    $this->assertEqual(['unsubscribed'], _webform_submit_newsletter($c, $v));

    // Radio yes.
    $v = 'yes';
    $this->assertEqual(['subscribed'], _webform_submit_newsletter($c, $v));

    // Not selected radio.
    $v = NULL;
    $this->assertEqual([''], _webform_submit_newsletter($c, $v));
  }

  public function testTable() {
    $export = function ($v) {
      return _webform_table_newsletter(NULL, $v);
    };
    $this->assertEqual(t('no change'), $export(NULL));
    $this->assertEqual(t('no change'), $export(['0']));
    $this->assertEqual(t('subscribed'), $export(['subscribed']));
    // Old format - backwards compatibility.
    $this->assertEqual(t('subscribed'), $export(['subscribed' => 'subscribed']));
    $this->assertEqual(t('unsubscribed'), $export(['unsubscribed']));
  }

  public function testCsvData() {
    $export = function ($v) {
      return _webform_csv_data_newsletter(NULL, [], $v);
    };
    $this->assertEqual(t('no change'), $export(NULL));
    $this->assertEqual(t('no change'), $export(['0']));
    $this->assertEqual(t('subscribed'), $export(['subscribed']));
    // Old format - backwards compatibility.
    $this->assertEqual(t('subscribed'), $export(['subscribed' => 'subscribed']));
    $this->assertEqual(t('unsubscribed'), $export(['unsubscribed']));
  }

}

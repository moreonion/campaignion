<?php

namespace Drupal\campaignion_newsletters;

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
    require_once drupal_get_path('module', 'webform') . '/includes/webform.components.inc';
    require_once drupal_get_path('module', 'campaignion_newsletters') . '/campaignion_newsletters.component.inc';
  }

  /**
   * Test whether rendering the edit form with the default configuration works.
   */
  public function testComponentDefaults() {
    $defaults = webform_component_invoke('newsletter', 'defaults');

    $expected_subset = array(
      'extra' => array(
        'opt_in_implied' => 1,
        'send_welcome' => 0,
        'optin_statement' => '',
      ),
    );
    $this->assertArraySubset($expected_subset, $defaults);

    $component = webform_component_invoke('newsletter', 'edit', $defaults);
    $this->assertEqual('textarea', $component['extra']['optin_statement']['#type']);
  }

  /**
   * Test normalizing input values from a checkbox.
   */
  public function testSubmitCheckbox() {
    $c['extra']['display'] = 'checkbox';

    // Not checked checkbox.
    $v['opt-in'] = 0;
    $this->assertEqual(['checkbox:no-change'], _webform_submit_newsletter($c, $v));

    // Checked checkbox.
    $v['opt-in'] = 'opt-in';
    $this->assertEqual(['checkbox:opt-in'], _webform_submit_newsletter($c, $v));
  }

  /**
   * Test normalizing input values from radios.
   */
  public function testSubmitRadios() {
    $c['extra']['display'] = 'radios';

    // Radio no.
    $v = 'opt-out';
    $this->assertEqual(['radios:opt-out'], _webform_submit_newsletter($c, $v));

    // Radio no change.
    $v = 'no-change';
    $this->assertEqual(['radios:no-change'], _webform_submit_newsletter($c, $v));

    // Radio yes.
    $v = 'opt-in';
    $this->assertEqual(['radios:opt-in'], _webform_submit_newsletter($c, $v));

    // Not selected radio.
    $v = NULL;
    $this->assertEqual(['radios:not-selected'], _webform_submit_newsletter($c, $v));
  }

  /**
   * Test rendering data for the table display.
   */
  public function testTable() {
    $export = function ($v) {
      return _webform_table_newsletter(NULL, $v);
    };
    $this->assertEqual(t('Unknown value'), $export(NULL));
    $this->assertEqual(t('Unknown value'), $export(['0']));
    $this->assertEqual(t('Checkbox opt-in'), $export(['checkbox:opt-in']));
    $this->assertEqual(t('Radio opt-in'), $export(['radios:opt-in']));
    $this->assertEqual(t('Radio opt-out'), $export(['radios:opt-out']));
    $this->assertEqual(t('Checkbox no change'), $export(['checkbox:no-change']));
    $this->assertEqual(t('Radio no change'), $export(['radios:no-change']));
    $this->assertEqual(t('Radio not selected (no change)'), $export(['radios:not-selected']));
  }

  /**
   * Test rendering data for CSV output.
   */
  public function testCsvData() {
    $export = function ($v) {
      return _webform_csv_data_newsletter(NULL, [], $v);
    };
    $this->assertEqual(t('Unknown value'), $export(NULL));
    $this->assertEqual(t('Unknown value'), $export(['0']));
    $this->assertEqual(t('Checkbox opt-in'), $export(['checkbox:opt-in']));
    $this->assertEqual(t('Radio opt-in'), $export(['radios:opt-in']));
    $this->assertEqual(t('Radio opt-out'), $export(['radios:opt-out']));
    $this->assertEqual(t('Checkbox no change'), $export(['checkbox:no-change']));
    $this->assertEqual(t('Radio no change'), $export(['radios:no-change']));
    $this->assertEqual(t('Radio not selected (no change)'), $export(['radios:not-selected']));
  }

  /**
   * Get the conditional form callback.
   */
  protected function getConditionalFormCallback() {
    $info = campaignion_newsletters_webform_conditional_operator_info();
    return $info['newsletter']['equal']['form callback'];
  }

  /**
   * Test conditional options with radios.
   */
  public function testConditionalOptionsRadios() {
    $fake_node['webform']['components'][1] = [
      'type' => 'newsletter',
      'extra' => [
        'display' => 'radios',
        'no_is_optout' => FALSE,
      ],
    ];
    $fake_node = (object) $fake_node;
    $form_callback = $this->getConditionalFormCallback();
    $forms = $form_callback($fake_node);
    $expected_select = '<select class="form-select"><option value="radios:opt-in">Radio opt-in</option><option value="radios:no-change">Radio no change</option><option value="radios:not-selected">Radio not selected (no change)</option></select>';
    $this->assertContains($expected_select, $forms[1]);
  }

}

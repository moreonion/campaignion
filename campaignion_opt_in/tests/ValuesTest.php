<?php

namespace Drupal\campaignion_opt_in;

require_once drupal_get_path('module', 'webform') . '/includes/webform.components.inc';

/**
 * Test the values helper class.
 */
class ValuesTest extends \DrupalUnitTestCase {

  /**
   * Generate a new opt_in component according to the parameters.
   */
  protected function getComponent($display, $no_is_optout, $disable_optin) {
    $component['type'] = 'opt_in';
    $component['extra'] = [
      'display' => $display,
      'no_is_optout' => $no_is_optout,
      'disable_optin' => $disable_optin,
    ];
    webform_component_defaults($component);
    return $component;
  }

  /**
   * Test getting options for all possible configurations.
   */
  public function testOptionsByComponent() {
    $self = $this;
    $unprefix = function ($x) {
      return explode(':', $x, 2)[1];
    };
    $options = function ($display, $no_is_optout, $disable_optin) use ($self, $unprefix) {
      $c = $self->getComponent($display, $no_is_optout, $disable_optin);
      return array_map($unprefix, array_keys(Values::optionsByComponent($c)));
    };
    list($i, $o, $n, $s) = ['opt-in', 'opt-out', 'no-change', 'not-selected'];
    $this->assertEqual([$i, $n], $options('checkbox', FALSE, FALSE));
    $this->assertEqual([$i, $o], $options('checkbox', TRUE, FALSE));
    $this->assertEqual([$n, $o], $options('checkbox', TRUE, TRUE));
    $this->assertEqual([$i, $n], $options('checkbox', FALSE, TRUE));
    $this->assertEqual([$i, $n], $options('checkbox-inverted', FALSE, FALSE));
    $this->assertEqual([$i, $o], $options('checkbox-inverted', TRUE, FALSE));
    $this->assertEqual([$n, $o], $options('checkbox-inverted', TRUE, TRUE));
    $this->assertEqual([$i, $n], $options('checkbox-inverted', FALSE, TRUE));
    $this->assertEqual([$i, $n, $s], $options('radios', FALSE, FALSE));
    $this->assertEqual([$i, $o, $s], $options('radios', TRUE, FALSE));
    $this->assertEqual([$i, $o, $s], $options('radios', TRUE, TRUE));
    $this->assertEqual([$i, $n, $s], $options('radios', FALSE, TRUE));
  }

  /**
   * Test getting checkbox values for all possible configurations.
   */
  public function testCheckboxValues() {
    $self = $this;
    $values = function ($display, $no_is_optout, $disable_optin) use ($self) {
      $c = $self->getComponent($display, $no_is_optout, $disable_optin);
      return Values::checkboxValues($c);
    };
    list($i, $o, $n) = ['opt-in', 'opt-out', 'no-change'];
    $this->assertEqual([$i, $n], $values('checkbox', FALSE, FALSE));
    $this->assertEqual([$i, $o], $values('checkbox', TRUE, FALSE));
    $this->assertEqual([$n, $o], $values('checkbox', TRUE, TRUE));
    $this->assertEqual([$i, $n], $values('checkbox', FALSE, TRUE));
    $this->assertEqual([$n, $i], $values('checkbox-inverted', FALSE, FALSE));
    $this->assertEqual([$o, $i], $values('checkbox-inverted', TRUE, FALSE));
    $this->assertEqual([$o, $n], $values('checkbox-inverted', TRUE, TRUE));
    $this->assertEqual([$n, $i], $values('checkbox-inverted', FALSE, TRUE));
  }

}

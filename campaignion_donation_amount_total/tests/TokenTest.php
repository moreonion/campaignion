<?php

namespace Drupal\campaignion_donation_amount_total;

use Upal\DrupalUnitTestCase;

/**
 * Test token replacement.
 */
class TokenTest extends DrupalUnitTestCase {

  /**
   * Test whether our tokens appear in the token info.
   */
  public function testTokenInfo() {
    $info = token_info();
    $this->assertNotEmpty($info['tokens']['submission']['amount-total']);
  }

  /**
   * Test token replacement.
   */
  public function testReplaceToken() {
    module_load_include('components.inc', 'webform', 'includes/webform'); //Loads a module include file.
    module_load_include('submissions.inc', 'webform', 'includes/webform');

    $components[1] = [ //adds a new array to components array on place 1?
      'type' => 'textfield',
      'form_key' => 'donation_amount_1',
    ];
    $components[2] = [ //adds a new array to components array on place 2?
      'type' => 'textfield',
      'form_key' => 'donation_amount_2',
    ];
    //iterates over array and assigns current key to $cid and current value to $component
    foreach ($components as $cid => &$component) {
      $component += [
        'pid' => 0,
        'cid' => $cid,
      ];
      webform_component_defaults($component);
    }
    //webform_node_defaults = "Default settings for a newly created webform node."
    //creates node of type webform, including the previously defined components
    $node = (object) ['type' => 'webform', 'nid' => NULL];
    $node->webform = ['components' => $components] + webform_node_defaults();
    node_object_prepare($node); //Prepares a node object for editing.

    module_load_include('submissions.inc', 'webform', 'includes/webform'); //why are we doing this twice? line 25
    $form_state['values']['submitted'][1][] = 10;
    $form_state['values']['submitted'][2][] = 5;
    $submission_1 = webform_submission_create($node, $GLOBALS['user'], $form_state);

    $replaced_1 = webform_replace_tokens('[submission:amount-total]', $node, $submission_1);
    $this->assertSame('15', $replaced_1);

    /*
     * test what happens when one donation amount field is empty ?
     */
    $form_state['values']['submitted'][2][] = NULL; //would NULL represent empty field?
    $submission_2 = webform_submission_create($node, $GLOBALS['user'], $form_state);

    $replaced_2 = webform_replace_tokens('[submission:amount-total]', $node, $submission_2);
    $this->assertSame('10', $replaced_2);
  }
}

<?php

namespace Drupal\campaignion\Forms;

class EmbeddedNodeForm {
  protected $embed_state;
  protected $form;
  public function __construct($node, &$form_state, $parents = array()) {
    form_load_include($form_state, 'inc', 'node', 'node.pages');
    $form_state += array('embedded' => array());
    drupal_array_set_nested_value($form_state['embedded'], $parents);
    $this->embed_state = &drupal_array_get_nested_value($form_state['embedded'], $parents);
    $this->embed_state['node'] = $node;
    $this->embed_state['build_info'] = array(
      'form_id' => $node->type . '_node_form',
      'base_form_id' => 'node_form',
    );
    $this->parents = $parents;
  }

  /**
   * Calls form alter hooks.
   * @see hook_form_FORM_ID_alter().
   * @see hook_form_BASE_FORM_ID_alter().
   */
  protected function alterForm(&$form, &$form_state) {
    $hooks = array('form');
    if (isset($form_state['build_info']['base_form_id'])) {
      $hooks[] = 'form_' . $form_state['build_info']['base_form_id'];
    }
    $form_id = $form_state['build_info']['form_id'];
    $hooks[] = 'form_' . $form_id;
    drupal_alter($hooks, $form, $form_state, $form_id);
  }

  protected function embedFieldGroups(&$form) {
    if (count($this->parents) > 0) {
      $embed_name = implode('][', $this->parents);
      $form['#tree'] = TRUE;
      foreach ($form as $key => &$element) {
        if ($key[0] != '#' && $element['#type'] == 'fieldset' && isset($element['#group'])) {
          $element['#group'] = $embed_name . '][' . $element['#group'];
        }
      }
    }
  }

  public function formArray() {
    $form = node_form(array(), $this->embed_state, $this->form_state['node']);
    $this->alterForm($form, $this->embed_state);
    $this->embedFieldGroups($form);
    return $form;
  }

  public function validate($form, &$form_state) {
    $form = &drupal_array_get_nested_value($form, $this->parents);
    node_form_validate($form, $this->embed_state);
  }

  public function submit($form, &$form_state) {
    $form = &drupal_array_get_nested_value($form, $this->parents);
    $submit_handlers = isset($form['#submit']) ? $form['#submit'] : FALSE;
    if ($submit_handlers) {
      unset($form['#submit']);
    }
    node_form_submit($form, $this->embed_state);
    if ($submit_handlers) {
      $form['#submit'] = $submit_handlers; unset($submit_handlers);
    }
  }
}

<?php

namespace Drupal\campaignion\Wizard;

class ThankyouStep extends WizardStep {
  protected $step = 'thank';
  protected $title = 'Thank you';
  protected function loadIncludes() {
    module_load_include('pages.inc', 'node');
    module_load_include('inc', 'webform', 'includes/webform.emails');
    module_load_include('inc', 'webform', 'includes/webform.components');
  }

  protected function pageForm(&$form_state, $index, $title, $prefix) {

    $action = $this->wizard->node;

    if (isset($form_state['node']) == TRUE) {
      unset($form_state['node']);
    }

    $template_default = array();
    $thank_you_pages =& $action->field_thank_you_pages['und'];

    if (isset($thank_you_pages[$index]['node_reference_nid'])) {
      $type = 'node';
      $node = node_load($thank_you_pages[$index]['node_reference_nid']);
      $old['redirect_url'] = '';
    }
    else {
      $type = 'redirect';
      $node = $this->wizard->prepareNode('thank_you_page');
      if (isset($thank_you_pages[$index]['redirect_url']) == FALSE) {
        $old['redirect_url'] = '';
      }
      else {
        $old['redirect_url'] = $thank_you_pages[$index]['redirect_url'];
      }
    }

    $form = array(
      '#type'  => 'fieldset',
      '#title' => $title,
    );
    $form['type'] = array(
      '#type'          => 'radio',
      '#title'         => t('Redirect supporters to a URL after the action'),
      '#return_value'  => 'redirect',
      '#default_value' => $type == 'redirect' ? 'redirect' : NULL,
      '#parents'       => array($prefix, 'type'),
    );
    $form['redirect_url'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Redirect URL'),
      '#states'        => array('visible' => array(":input[name=\"${prefix}[type]\"]" => array('value' => 'redirect'))),
      '#default_value' => $old['redirect_url'],
    );
    $form['or2'] = array(
      '#type'   => 'markup',
      '#markup' => '<div class="thank-you-outer-or"><span class="thank-you-line-or"><span class="thank-you-text-or">' . t('or') . '</span></span></div>',
    );
    $form['type3'] = array(
      '#type'          => 'radio',
      '#title'         => t('Create new thank you page'),
      '#return_value'  => 'node',
      '#default_value' => $type == 'node' ? 'node' : NULL,
      '#parents'       => $form['type']['#parents'],
    );
    $node_form = array(
      '#type'    => 'container',
      '#states'  => array('visible' => array(":input[name=\"${prefix}[type]\"]" => array('value' => 'node'))),
      '#tree'    => TRUE,
      '#parents' => array('node_form'),
    ) + node_form(array(), $form_state, $node);

    $node_form['title']['#required'] = FALSE;
    // don't publish per default
    $node_form['options']['status']['#default_value'] = 0;
    $node_form['options']['promote']['#default_value'] = 0;
    foreach ($node_form as $key => &$element) {
      if (    $key[0] != '#'
           && $element['#type'] == 'fieldset'
           && isset($element['#group'])) {
        $element['#group'] = 'node_form][' . $element['#group'];
      }
    }

    foreach (array('actions', 'options', 'revision_information', 'author', 'redirect', 'additional_settings', 'field_flexible', 'field_intro') as $key) {
      $node_form[$key]['#access'] = FALSE;
    }

    // order the form fields
    $node_form['field_main_image']['#weight'] = -15;
    $node_form['field_share_light']['#weight'] = -10;
    $node_form['field_main_image']['#attributes']['class'][] = 'sidebar-narrow-right';
    $node_form['#tree'] = TRUE;
    unset($node_form['#parents']);

    $form['node_form'] =& $node_form;
    $form['#attributes']['class'][] = 'thank-you-node-wrapper';

    $form['#tree'] = TRUE;

    return $form;
  }

  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);

    // check if double opt in was enabled and if yes provide a 2nd thank you page
    $thank_you_class = 'half-left';
    if (campaignion_wizard_has_double_optin($this->wizard->node->nid) != FALSE) {
      $form['submission_node'] = $this->pageForm($form_state, 0, t('Submission page'), 'submission_node');
      $form['submission_node']['#attributes']['class'][] = 'half-left';
      $thank_you_class = 'half-right';
      $form['#attributes']['class'][] = 'two-halfs';
    }

    $form['thank_you_node'] = $this->pageForm($form_state, 1, t('Thank you page'), 'thank_you_node');
    $form['thank_you_node']['#attributes']['class'][] = $thank_you_class;

    $form['#tree'] = TRUE;
    $form['wizard_head']['#tree'] = FALSE;

    return $form;
  }

  public function checkDependencies() {
    return isset($this->wizard->node->nid);
  }

  public function validateStep($form, &$form_state) {
    $values =& $form_state['values'];
    unset($form_state['values']);
    $thank_you_pages = array('thank_you_node');
    if (campaignion_wizard_has_double_optin($this->wizard->node->nid) != FALSE) {
      $thank_you_pages[] = 'submission_node';
    }
    foreach ($thank_you_pages as $page) {
      if (in_array($values[$page]['type'], array('node', 'redirect')) == FALSE) {
        form_set_error('type', t('You have to create either a thank you page or provide a redirect.'));
      }
      if ($values[$page]['type'] == 'node') {
        $form_state['values'] =& $values[$page]['node_form'];
        node_form_validate($form, $form_state);
        if (empty($values[$page]['node_form']['title'])) {
          form_set_error("$page][node_form][title", t('!name field is required.', array('!name' => 'Title')));
        }
      }
      elseif ($values[$page]['type'] == 'redirect') {
        if (empty($values[$page]['redirect_url'])) {
          form_set_error('redirect_url', t('You need to provide either a redirect url or create a thank you page.'));
        }
      }
    }
    $form_state['values'] =& $values;
  }

  public function submitStep($form, &$form_state) {
    $values =& $form_state['values'];
    unset($form_state['values']);
    $action = $this->wizard->node;

    $thank_you_pages = array('thank_you_node' => 1);
    if (campaignion_wizard_has_double_optin($this->wizard->node->nid) != FALSE) {
      $thank_you_pages['submission_node'] = 0;
    }

    foreach(array(0,1) as $index) {
      $action->field_thank_you_pages[LANGUAGE_NONE][$index]['node_reference_nid'] = NULL;
      $action->field_thank_you_pages[LANGUAGE_NONE][$index]['redirect_url'] = NULL;
    }

    foreach($thank_you_pages as $page => $index) {
      if ($values[$page]['type'] == 'node') {
        $form_state['values'] =& $values[$page]['node_form'];

        $submit_handlers = $form['#submit'];
        unset($form['#submit']);
        node_form_submit($form, $form_state);

        $form['#submit'] = $submit_handlers;
        unset($submit_handlers);

        $action->field_thank_you_pages[LANGUAGE_NONE][$index]['node_reference_nid'] = $form_state['values']['nid'];
        $action->field_thank_you_pages[LANGUAGE_NONE][$index]['redirect_url']       = NULL;
        $path = 'node/' . $form_state['values']['nid'];
        if (count($thank_you_pages) == 1) {
          $action->webform['redirect_url'] = $path;
        }
        else {
          if ($page == 'thank_you_node') {
            campaignion_wizard_set_confirmation_redirect_url($this->wizard->node->nid, $path);
          }
          else {
            $action->webform['redirect_url'] = $path;
          }
        }
      }
      else {
        $action->field_thank_you_pages[LANGUAGE_NONE][$index]['node_reference_nid'] = NULL;
        $action->field_thank_you_pages[LANGUAGE_NONE][$index]['redirect_url']       = $values[$page]['redirect_url'];

        if (count($thank_you_pages) == 1) {
          $action->webform['redirect_url'] = $values[$page]['redirect_url'];
        }
        else {
          if ($page == 'thank_you_node') {
            campaignion_wizard_set_confirmation_redirect_url($this->wizard->node->nid, $values[$page]['redirect_url']);
          }
          else {
            $action->webform['redirect_url'] = $values[$page]['redirect_url'];
          }
        }
      }
    }

    node_save($action);

    $form_state['values'] =& $values;
  }

  public function status() {
    $thank_you_pages = field_get_items('node', $this->wizard->node, 'field_thank_you_pages');

    $msg = t("After your supporters submitted their filled out form ");

    if (   isset($thank_you_pages[0]['redirect_url'])       == TRUE
        || isset($thank_you_pages[0]['node_reference_nid']) == TRUE) {

      if (isset($thank_you_pages[0]['redirect_url']) == TRUE) {
        $msg .= t(
          "you're redirecting them to !link .",
          array(
            '!link' => l(
              $thank_you_pages[0]['redirect_url'],
              $thank_you_pages[0]['redirect_url']
            )
          )
        );
      }
      else {
        $node = node_load($thank_you_pages[0]['node_reference_nid']);
        $msg .= t('they\'ll see your submission page !node.', array('!node' => l($node->title, 'node/' . $node->nid)));
      }
      $msg .= t("<br>After your supporters clicked the confirmation link ");
    }

    if (isset($thank_you_pages[1]['redirect_url']) == TRUE) {
      $msg .= t(
        "you're redirecting them to !link .",
        array(
          '!link' => l(
            $thank_you_pages[1]['redirect_url'],
            $thank_you_pages[1]['redirect_url']
          )
        )
      );
    }
    else {
      $node = node_load($thank_you_pages[1]['node_reference_nid']);
      $msg .= t('they\'ll see your thank you page !node.', array('!node' => l($node->title, 'node/' . $node->nid)));
    }

    return array(
      'caption' => t('Thank you page'),
      'message' => $msg,
    );
  }
}

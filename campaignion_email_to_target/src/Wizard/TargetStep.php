<?php

namespace Drupal\campaignion_email_to_target\Wizard;

use \Drupal\campaignion\Forms\EntityFieldForm;
use \Drupal\campaignion_email_to_target\Api\Client;

class TargetStep extends \Drupal\campaignion_wizard\WizardStep {
  protected $step  = 'target';
  protected $title = 'Target';

  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);
    $field = $this->wizard->parameters['email_to_target']['options_field'];
    $this->fieldForm = new EntityFieldForm('node', $this->wizard->node, [$field]);
    $form += $this->fieldForm->formArray($form_state);

    $settings = [];
    $client = Client::fromConfig();
    $token = $client->getAccessToken();
    $settings['endpoints']['e2t-api'] = [
      'url' => $client->getEndpoint(),
      'token' => $token
    ];

    $settings = ['campaignion_email_to_target' => $settings];
    $dir = drupal_get_path('module', 'campaignion_email_to_target');
    $form['#attached']['js'][] = ['data' => $settings, 'type' => 'setting'];
    $form['#attached']['js'][] = [
      'data' => $dir . '/js/datasets_app/datasets_app.vue.min.js',
      'scope' => 'footer',
      'preprocess' => FALSE,
    ];
    $form['#attached']['css'][] = [
      'data' => $dir . '/css/datasets_app/datasets_app.css',
      'group' => 'CSS_DEFAULT',
    ];

    return $form;
  }

  public function validateStep($form, &$form_state) {
    $this->fieldForm->validate($form, $form_state);
  }

  public function submitStep($form, &$form_state) {
    $this->fieldForm->submit($form, $form_state);
  }

  public function checkDependencies() {
    return isset($this->wizard->node->nid);
  }
}

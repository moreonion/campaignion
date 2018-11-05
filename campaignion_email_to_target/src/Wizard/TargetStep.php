<?php

namespace Drupal\campaignion_email_to_target\Wizard;

use \Drupal\campaignion\Forms\EntityFieldForm;
use \Drupal\campaignion_email_to_target\Api\Client;

class TargetStep extends \Drupal\campaignion_wizard\WizardStep {
  protected $step  = 'target';
  protected $title = 'Target';
  protected $api;

  public function __construct($wizard, $api = NULL) {
    parent::__construct($wizard);
    $this->api = $api ? $api : Client::fromConfig();
  }

  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);
    $field = $this->wizard->parameters['email_to_target']['options_field'];
    $this->fieldForm = new EntityFieldForm('node', $this->wizard->node, [$field]);
    $form += $this->fieldForm->formArray($form_state);


    $settings = [];
    $settings['contactPrefix'] = 'contact.';  // identifies contact fields within a dataset’s attributes
    $settings['standardColumns'] = [          // these are posted by the front end if a new dataset is added
      [
        'key' => 'email',
        'description' => '',
        'title' => 'Email address',
      ],
      [
        'key' => 'title',
        'description' => '',
        'title' => 'Title',
      ],
      [
        'key' => 'first_name',
        'description' => '',
        'title' => 'First name',
      ],
      [
        'key' => 'last_name',
        'description' => '',
        'title' => 'Last name',
      ],
      [
        'key' => 'salutation',
        'description' => 'Full name and titles',
        'title' => 'Salutation',
      ],
      [
        'key' => 'display_name',
        'title' => 'Display name of target',
        'description' => 'The name/description of the target that the users see',
      ],
    ];
    $settings['validations'] = [              // used by the front end, a set of 'key' => 'regex' pairs
      'email' => '^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$', // backslashes have to be escaped so JS won’t interpret them as escape sequence.
      'first_name' => '\\S+',
      'last_name' => '\\S+',
      'salutation' => '\\S+',
    ];
    $settings['endpoints']['e2t-api'] = [
      'url' => $this->api->getEndpoint(),
      'token' => $this->api->getAccessToken(),
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

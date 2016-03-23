<?php

/**
 * Implementation of campaignion_newsletters_optivo_form_campaignion_newsletters_admin_settings_alter().
 */
function _campaignion_newsletters_optivo_form_campaignion_newsletters_admin_settings_alter(&$form, &$form_state) {

  $form['optivo'] = array(
    '#type' => 'fieldset',
    '#title' => t('Optivo'),
    '#weight' => 1,
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
  );
  $form['optivo']['credentials']= array(
    '#type' => 'fieldset',
    '#title' => t('Credentials'),
    '#prefix' => '<div id="optivo-credentials-wrapper">',
    '#suffix' => '</div>',
  );

  $fs = &$form['optivo']['credentials'];
  $keys = variable_get('optivo_credentials', array());
  if (empty($form_state['optivo_new_credentials'])) {
    $form_state['optivo_new_credentials'] = count($keys) ? 0 : 1;
  }

  foreach ($keys as $name => $data) {
    $fs[$name]['mandatorId'] = array(
      '#type' => 'textfield',
      '#default_value' => $data['mandatorId'],
      '#title' => t('Client ID'),
    );
    $fs[$name]['name'] = array(
      '#default_value' => $name,
      '#disabled' => TRUE,
      '#type' => 'machine_name',
      '#title' => t('Machine name'),
      '#machine_name' => array(
        'exists' => 'campaignion_newsletters_optivo_get_key',
        'source' => array('optivo', 'credentials', $name, 'mandatorId')
      )
    );
    $fs[$name]['username'] = array(
      '#type' => 'textfield',
      '#default_value' => $data['username'],
      '#title' => t('User name'),
    );
    $fs[$name]['password'] = array(
      '#type' => 'textfield',
      '#default_value' => $data['password'],
      '#title' => t('Password'),
    );
  }

  if (!empty($form_state['optivo_new_credentials'])) {
    for ($i = 1; $i <= $form_state['optivo_new_credentials']; $i++) {
      $name = 'new_' . $i;
      $fs [$name] ['mandatorId'] = array (
        '#type' => 'textfield',
        '#default_value' => '',
        '#title' => t ( 'Client ID' ),
        '#description' => t ( 'The Optivo client ID. Go to "API overview" in
            the Optivo "Administration" menu and switch to the "SOAP API" tab
            to find out your client ID.' )
      );
      $fs[$name]['name'] = array(
        '#default_value' => '',
        '#required' => FALSE,
        '#type' => 'machine_name',
        '#title' => t('Machine name'),
        '#machine_name' => array(
          'exists' => 'campaignion_newsletters_optivo_get_key',
          'source' => array('optivo', 'credentials', $name, 'mandatorId')
        )
      );
      $fs [$name] ['username'] = array (
        '#type' => 'textfield',
        '#default_value' => '',
        '#title' => t ( 'User name' ),
        '#description' => t ( 'Name of an Optivo user who has
            permission to use the API (= "INTERFACE_WEBSERVICE" permission).' )
      );
      $fs [$name] ['password'] = array (
        '#type' => 'textfield',
        '#default_value' => '',
        '#title' => t ( 'Password' ),
        '#description' => t ( 'The password for the Optivo user.' )
      );
    }
  }
  $fs['add_more'] = array(
    '#type' => 'submit',
    '#value' => t('Add another client'),
    '#limit_validation_errors' => array(),
    '#ajax' => array(
       'callback' => 'campaignion_newsletters_optivo_admin_ajax',
       'wrapper' => 'optivo-credentials-wrapper',
    ),
    '#submit' => array('campaignion_newsletters_optivo_admin_add_more_submit'),
  );

  array_unshift(
    $form['#submit'],
    'campaignion_newsletters_optivo_admin_submit'
  );
  array_unshift(
    $form['#validate'],
    'campaignion_newsletters_optivo_admin_validate'
  );
}

/**
 * Ajax callback for the add-more keys button.
 */
function campaignion_newsletters_optivo_admin_ajax($form, &$form_state) {
  return $form['optivo']['credentials'];
}

/**
 * Submit callback for the add-more keys button.
 */
function campaignion_newsletters_optivo_admin_add_more_submit($form, &$form_state) {
  $form_state['optivo_new_credentials']++;
  $form_state['rebuild'] = TRUE;
}

/**
 * Validate callback for the admin form.
 */
function campaignion_newsletters_optivo_admin_validate($form, &$form_state) {
  $keys = &$form_state['values']['optivo']['credentials'];
  foreach ($keys as $key => $data) {
    if (empty($data['name']) && empty($data['mandatorId']) && empty($data['username'])  && empty($data['password'])) {
      continue;
    }
    if (empty($data['name'])) {
      form_set_error('optivo][credentials][' . $key . '][name', t('The Optivo client has to have a unique name.'));
    }
    if (empty($data['mandatorId'])) {
      form_set_error('optivo][credentials][' . $key . '][mandatorId', t('Please provide your Optivo client ID.'));
    }
    if (empty($data['username'])) {
      form_set_error('optivo][credentials][' . $key . '][username', t('Please enter an Optivo user name.'));
    }
    if (empty($data['password'])) {
      form_set_error('optivo][credentials][' . $key . '][password', t('Please provide the Optivo user’s password.'));
    }
  }
}

/**
 * Submit callback for the admin form.
 */
function campaignion_newsletters_optivo_admin_submit($form, &$form_state) {
  $keys = array();
  foreach ($form_state['values']['optivo']['credentials'] as $data) {
    if (!empty($data['mandatorId']) && !empty($data['username']) && !empty($data['password'])) {
      $name = $data['name'];
      unset($data['name']);
      $keys[$name] = $data;
    }
  }
  variable_set('optivo_credentials', $keys);

  // Hide our values from the general submit handler.
  unset($form_state['values']['optivo']);
}

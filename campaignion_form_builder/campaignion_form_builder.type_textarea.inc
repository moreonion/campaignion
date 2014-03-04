<?php

/**
 * Invoked by campaignion_form_builder_form_builder_types().
 */
function _campaignion_form_builder_form_builder_types_textarea() {
  $fields = array();

  $unique_ae = array(
    'palette_group' => 'advocacy_engine',
    'unique'        => TRUE,
    'properties'    => array('default_value'),
    'default'       => _form_builder_webform_default('textarea'),
  );

  if (_campaignion_form_builder_is_email_protest() != FALSE) {

    $fields['email_body'] = array(
      'title'  => t('Email body'),
      'weight' => -80,
    ) + $unique_ae;
    $fields['email_body']['default']['#title'] = t('Email body');
    $fields['email_body']['default']['#default_value'] = '';
    $fields['email_body']['palette_group'] = 'email_protest';
  }

  return $fields;
}

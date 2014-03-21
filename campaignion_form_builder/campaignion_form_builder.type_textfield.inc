<?php

/**
 * Invoked by campaignion_form_builder_form_builder_types().
 */
function _campaignion_form_builder_form_builder_types_textfield() {
  $fields = array();

  $unique_ae = array(
    'palette_group' => 'supporter_data',
    'unique'        => TRUE,
    'properties'    => array('default_value'),
    'default'       => _form_builder_webform_default('textfield'),
  );

  $fields['first_name'] = array(
    'title'  => t('First name'),
    'weight' => -80,
  ) + $unique_ae;
  $fields['first_name']['default']['#title'] = t('First name');
  $fields['first_name']['default']['#default_value'] = '%get[p3]';

  $fields['last_name'] = array(
    'title'  => t('Last name'),
    'weight' => -79,
  ) + $unique_ae;
  $fields['last_name']['default']['#title'] = t('Last name');
  $fields['last_name']['default']['#default_value'] = '%get[p4]';

  $fields['title'] = array(
    'title'  => t('Title'),
    'weight' => -77,
  ) + $unique_ae;
  $fields['title']['default']['#title'] = t('Title');
  $fields['title']['default']['#default_value'] = '%get[p4]';

  $fields['date_of_birth'] = array(
    'title'  => t('Date of birth'),
    'weight' => -76,
  ) + $unique_ae;
  $fields['date_of_birth']['default']['#title'] = t('Date of birth');
  $fields['date_of_birth']['default']['#default_value'] = '%get[p6]';
  $fields['date_of_birth']['default']['#description'] = t('Bitte folgendermaßen eintragen: 16/9/1983');

  $fields['street_address'] = array(
    'title' => t('Street address'),
    'weight' => -75,
  ) + $unique_ae;
  $fields['street_address']['default']['#title'] = t('Street address');
  $fields['street_address']['default']['#default_value'] = '%get[p7]';

  $fields['zip_code'] = array(
    'title' => t('ZIP code'),
    'weight' => -74,
  ) + $unique_ae;
  $fields['zip_code']['default']['#title'] = t('ZIP code');
  $fields['zip_code']['default']['#default_value'] = '%get[p8]';

  $fields['city'] = array(
    'title' => t('City'),
    'weight' => -72,
  ) + $unique_ae;
  $fields['city']['default']['#title'] = t('City');
  $fields['city']['default']['#default_value'] = '%get[p10]';

  $fields['phone_number'] = array(
    'title' => t('Phone number'),
    'weight' => -71,
  ) + $unique_ae;
  $fields['phone_number']['default']['#title'] = t('Phone number');
  $fields['phone_number']['default']['#default_value'] = '%get[p11]';

  $fields['mobile_number'] = array(
    'title' => t('Mobile number'),
    'weight' => -70,
  ) + $unique_ae;
  $fields['mobile_number']['default']['#title'] = t('Mobile number');
  $fields['mobile_number']['default']['#default_value'] = '%get[p12]';

  return $fields;
}

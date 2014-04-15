<?php

/**
 * Invoked by campaignion_form_builder_form_builder_types().
 */
function _campaignion_form_builder_form_builder_types_select() {
  $fields = array();

  $defaults = array(
    'properties' => array(
      'default_value',
      'options',
      'multiple',
    ),
    'default'       => _form_builder_webform_default('select', array('aslist' => TRUE, 'multiple' => FALSE)),
    'palette_group' => 'supporter_data',
    'unique'        => TRUE,
  );
  $defaults['default']['#form_builder']['element_type'] = 'select';

  $fields['salutation'] = array(
    'title'  => t('Salutation'),
    'weight' => -82,
  ) + $defaults;
  $fields['salutation']['properties'][] = 'other_option';
  $fields['salutation']['properties'][] = 'other_text';
  $fields['salutation']['default']['#options']       = array('fam' => t('Family'), 'mrs' => t('Mrs'), 'mr' => t('Mr'));
  $fields['salutation']['default']['#default_value'] = '%get[p1]';
  $fields['salutation']['default']['#title']         = t('Salutation');
  $fields['salutation']['default']['#other_option']  = TRUE;

  $fields['gender'] = array(
    'title'  => t('Gender'),
    'weight' => -81,
  ) + $defaults;
  $fields['gender']['default']['#options']       = array('f' => t('female'), 'm' => t('male'), 'o' => t('other'));
  $fields['gender']['default']['#default_value'] = '%get[p2]';
  $fields['gender']['default']['#title']         = t('Gender');

  $fields['country'] = array(
    'title'  => t('Country'),
    'weight' => -73,
  ) + $defaults;
  include_once(drupal_get_path('module', 'webform') . '/includes/webform.options.inc');
  $country_list = webform_options_countries(NULL, NULL, NULL, NULL);
  $fields['country']['default']['#options']       = $country_list;
  $fields['country']['default']['#default_value'] = '%get[p9]';
  $fields['country']['default']['#title']         = t('Country');

  $fields['email_newsletter'] = array(
    'title'  => t('Newsletter'),
    'weight' => -69,
  ) + $defaults;
  $fields['email_newsletter']['default']['#options']       = array('1' => t('Yes, I\'d like to get email newsletters'));
  $fields['email_newsletter']['default']['#default_value'] = 1;
  $fields['email_newsletter']['default']['#title']         = t('Email Newsletter');
  $fields['email_newsletter']['default']['#type']          = 'checkboxes';
  $fields['email_newsletter']['default']['#form_builder']['element_type'] = 'checkboxes';

  $fields['direct_mail_newsletter'] = array(
    'title'  => t('Direct Mail'),
    'weight' => -68,
  ) + $defaults;
  $fields['direct_mail_newsletter']['default']['#options']       = array('1' => t('Yes, I\'d like to get direct mail newsletters'));
  $fields['direct_mail_newsletter']['default']['#default_value'] = 1;
  $fields['direct_mail_newsletter']['default']['#title']         = t('Direct Mail Newsletter');
  $fields['direct_mail_newsletter']['default']['#type']          = 'checkboxes';
  $fields['email_newsletter']['default']['#form_builder']['element_type'] = 'checkboxes';

  return $fields;
}

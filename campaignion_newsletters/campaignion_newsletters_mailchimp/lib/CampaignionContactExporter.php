<?php

namespace Drupal\campaignion_newsletters_mailchimp;

use \Drupal\campaignion\Contact;
use \Drupal\campaignion\CRM\ExporterBase;
use \Drupal\campaignion\CRM\Export\SingleValueField;
use \Drupal\campaignion\CRM\Export\WrapperField;
use \Drupal\campaignion\CRM\Export\MappedWrapperField;
use \Drupal\campaignion\CRM\Export\DateField;
use \Drupal\campaignion\CRM\Export\KeyedField;
use \Drupal\campaignion\CRM\Export\TagsField;

class CampaignionContactExporter extends ExporterBase {
  public function __construct(Contact $contact) {
    $map['EMAIL'] = new WrapperField('email');
    $map['FNAME'] = new SingleValueField('first_name');
    $map['LNAME'] = new SingleValueField('last_name');
    $map['SALUTATION'] = new WrapperField('field_salutation');
    $map['TITLE'] = new WrapperField('field_title');
    $map['GENDER'] = new WrapperField('field_gender');
    $map['DATE_OF_BIRTH'] = new DateField('field_date_of_birth', '%Y-%m-%d');
    $map['STREET'] = new KeyedField('field_address', 'thoroughfare');
    $map['COUNTRY'] = new KeyedField('field_address', 'country');
    $map['ZIP'] = new KeyedField('field_address', 'postal_code');
    $map['CITY'] = new KeyedField('field_address', 'locality');
    $map['REGION'] = new KeyedField('field_address', 'administrative_area');
    $map['LANGUAGE'] = new WrapperField('field_preferred_language');
    $map['CREATED'] = new DateField('created', '%Y-%m-%d');
    $map['UPDATED'] = new DateField('updated', '%Y-%m-%d');
    $map['TAGS'] = new TagsField('supporter_tags');
    parent::__construct($map);
    $this->setContact($contact);
  }
}

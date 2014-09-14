<?php

namespace Drupal\campaignion_newsletters_cleverreach;

use \Drupal\campaignion\Contact;
use \Drupal\campaignion\CRM\ExporterBase;
use \Drupal\campaignion\CRM\Export\SingleValueField;
use \Drupal\campaignion\CRM\Export\WrapperField;
use \Drupal\campaignion\CRM\Export\MappedWrapperField;
use \Drupal\campaignion\CRM\Export\DateField;
use \Drupal\campaignion\CRM\Export\KeyedField;
use \Drupal\campaignion\CRM\Export\TagsField;

class CampaignionContactExporter implements ExporterBase {
  public function __construct(Contact $contact) {
    $map['email'] = new WrapperField('email');
    $map['salutation'] = new WrapperField('field_salutation');
    $map['firstname'] = new SingleValueField('first_name');
    $map['lastname'] = new SingleValueField('last_name');
    $map['title'] = new WrapperField('field_title');
    $map['gender'] = new WrapperField('field_gender');
    $map['date_of_birth'] = new DateField('field_date_of_birth', '%Y-%m-%d');
    $map['street'] = new KeyedField('field_address', 'thoroughfare');
    $map['country'] = new KeyedField('field_address', 'country');
    $map['zip'] = new KeyedField('field_address', 'postal_code');
    $map['city'] = new KeyedField('field_address', 'locality');
    $map['region'] = new KeyedField('field_address', 'administrative_area');
    $map['language'] = new WrapperField('field_preferred_language');
    $map['created'] = new DateField('created', '%Y-%m-%d');
    $map['updated'] = new DateField('updated', '%Y-%m-%d');
    $map['tags'] = new TagsField('supporter_tags', TRUE);
    parent::__construct($map);
    $this->setContact($contact);
  }
}

<?php

namespace Drupal\campaignion_newsletters_cleverreach;

use \Drupal\campaignion\Contact;
use \Drupal\campaignion\CRM\Import\Source\SourceInterface;
use \Drupal\campaignion\CRM\Export\SingleValueField;
use \Drupal\campaignion\CRM\Export\WrapperField;
use \Drupal\campaignion\CRM\Export\MappedWrapperField;
use \Drupal\campaignion\CRM\Export\DateField;
use \Drupal\campaignion\CRM\Export\KeyedField;

class CampaignionContactExporter implements SourceInterface {
  protected $map;
  public function __construct(Contact $contact) {
    $wrappedContact = $contact->wrap();
    $this->map = array();
    $this->map['email'] = new WrapperField($wrappedContact, 'email');
    $this->map['salutation'] = new WrapperField($wrappedContact, 'field_salutation');
    $this->map['firstname'] = new SingleValueField($contact, 'first_name');
    $this->map['lastname'] = new SingleValueField($contact, 'last_name');
    $this->map['title'] = new WrapperField($wrappedContact, 'field_title');
    $this->map['gender'] = new WrapperField($wrappedContact, 'field_gender');
    $this->map['date_of_birth'] = new DateField($wrappedContact, 'field_date_of_birth', '%Y-%m-%d');
    $this->map['street'] = new KeyedField($wrappedContact, 'field_address', 'thoroughfare');
    $this->map['country'] = new KeyedField($wrappedContact, 'field_address', 'country');
    $this->map['zip'] = new KeyedField($wrappedContact, 'field_address', 'postal_code');
    $this->map['city'] = new KeyedField($wrappedContact, 'field_address', 'locality');
    $this->map['region'] = new KeyedField($wrappedContact, 'field_address', 'administrative_area');
    $this->map['language'] = new WrapperField($wrappedContact, 'field_preferred_language');
    $this->map['created'] = new DateField($wrappedContact, 'created', '%Y-%m-%d');
    $this->map['updated'] = new DateField($wrappedContact, 'updated', '%Y-%m-%d');
    $this->map['tags'] = new TagExporter($wrappedContact, 'supporter_tags');
  }

  public function value($key) {
    if (isset($this->map[$key])) {
      return $this->map[$key]->value();
    }
    return NULL;
  }
}

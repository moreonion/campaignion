<?php

namespace Drupal\campaignion_manage;

use \Drupal\campaignion\Contact;
use \Drupal\campaignion\CRM\Import\Source\SourceInterface;
use \Drupal\campaignion\CRM\Export\SingleValueField;
use \Drupal\campaignion\CRM\Export\WrapperField;
use \Drupal\campaignion\CRM\Export\DateField;
use \Drupal\campaignion\CRM\Export\AddressField;
use \Drupal\campaignion\CRM\Export\TagField;

class CampaignionContactExporter implements SourceInterface {
  protected $map;
  public function __construct(Contact $contact, array $address_mapping) {
    $wrappedContact = $contact->wrap();
    $this->map = array();
    $this->map['redhen_contact_email']         = new WrapperField($wrappedContact, 'email');
    $this->map['field_salutation']             = new WrapperField($wrappedContact, 'field_salutation');
    $this->map['first_name']                   = new SingleValueField($contact, 'first_name');
    $this->map['middle_name']                  = new SingleValueField($contact, 'middle_name');
    $this->map['last_name']                    = new SingleValueField($contact, 'last_name');
    $this->map['field_title']                  = new WrapperField($wrappedContact, 'field_title');
    $this->map['field_gender']                 = new WrapperField($wrappedContact, 'field_gender');
    $this->map['field_date_of_birth']          = new DateField($wrappedContact, 'field_date_of_birth', '%Y-%m-%d');
    $this->map['field_address']                = new AddressField($wrappedContact, 'field_address', $address_mapping);
    $this->map['created']                      = new DateField($wrappedContact, 'created', '%Y-%m-%d');
    $this->map['updated']                      = new DateField($wrappedContact, 'updated', '%Y-%m-%d');
    $this->map['field_ip_adress']              = new WrapperField($wrappedContact, 'field_ip_adress');
    $this->map['field_phone_number']           = new WrapperField($wrappedContact, 'field_phone_number');
    $this->map['field_direct_mail_newsletter'] = new WrapperField($wrappedContact, 'field_direct_mail_newsletter');
    $this->map['field_social_network_links']   = new WrapperField($wrappedContact, 'field_social_network_links');
    $this->map['supporter_tags']               = new TagField($wrappedContact, 'supporter_tags');
    $this->map['field_preferred_language']     = new WrapperField($wrappedContact, 'field_preferred_language');
  }

  public function value($key) {
    if (isset($this->map[$key])) {
      return $this->map[$key]->value();
    }
    return NULL;
  }
}

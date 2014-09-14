<?php

namespace Drupal\campaignion_manage;

use \Drupal\campaignion\Contact;
use \Drupal\campaignion\CRM\ExporterBase;
use \Drupal\campaignion\CRM\Export\SingleValueField;
use \Drupal\campaignion\CRM\Export\WrapperField;
use \Drupal\campaignion\CRM\Export\DateField;
use \Drupal\campaignion\CRM\Export\AddressField;
use \Drupal\campaignion\CRM\Export\TagsField;

class CampaignionContactExporter extends ExporterBase {
  public function __construct(Contact $contact, array $address_mapping) {
    $map['redhen_contact_email']         = new WrapperField('email');
    $map['field_salutation']             = new WrapperField('field_salutation');
    $map['first_name']                   = new SingleValueField('first_name');
    $map['middle_name']                  = new SingleValueField('middle_name');
    $map['last_name']                    = new SingleValueField('last_name');
    $map['field_title']                  = new WrapperField('field_title');
    $map['field_gender']                 = new WrapperField('field_gender');
    $map['field_date_of_birth']          = new DateField('field_date_of_birth', '%Y-%m-%d');
    $map['field_address']                = new AddressField('field_address', $address_mapping);
    $map['created']                      = new DateField('created', '%Y-%m-%d');
    $map['updated']                      = new DateField('updated', '%Y-%m-%d');
    $map['field_ip_adress']              = new WrapperField('field_ip_adress');
    $map['field_phone_number']           = new WrapperField('field_phone_number');
    $map['field_direct_mail_newsletter'] = new WrapperField('field_direct_mail_newsletter');
    $map['field_social_network_links']   = new WrapperField('field_social_network_links');
    $map['supporter_tags']               = new TagsField('supporter_tags');
    $map['field_preferred_language']     = new WrapperField('field_preferred_language');
    parent::__construct($map);
    $this->setContact($contact);
  }
}

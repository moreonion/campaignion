<?php

namespace Drupal\campaignion_newsletters_mailchimp;

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
    $this->map['EMAIL'] = new WrapperField($wrappedContact, 'email');
    $this->map['FNAME'] = new SingleValueField($contact, 'first_name');
    $this->map['LNAME'] = new SingleValueField($contact, 'last_name');
  }

  public function value($key) {
    if (isset($this->map[$key])) {
      return $this->map[$key]->value();
    }
    return NULL;
  }
}

<?php

namespace Drupal\campaignion\CRM\Import;

use \Drupal\campaignion\Contact;
use \Drupal\campaignion\CRM\Import\Source\SourceInterface;

class MappedImport {
  public function __construct() {
    $this->mappings = array(
      new Field\Field('field_gender',     'gender'),
      new Field\Field('field_salutation', 'salutation'),
      new Field\Field('field_title',      'title'),
      new Field\Field('first_name'),
      new Field\Field('last_name'),
      new Field\Date('field_date_of_birth',    'date_of_birth'),
      new Field\Address('field_address', array(
        'thoroughfare'        => 'street_address',
        'postal_code'         => 'zip_code',
        'locality'            => 'city',
        'administrative_area' => 'state',
        'country'             => 'country',
      )),
      new Field\Phone('field_phone_number', 'phone_number'),
      new Field\Phone('field_phone_number', 'mobile_number'),
      new Field\EmailBulk('redhen_contact_email', 'email', 'email_newsletter'),
      new Field\Field('field_direct_mail_newsletter', 'direct_mail_newsletter'),
    );
  }

  public function import(SourceInterface $source, $contact_type = 'contact') {
    if (!($email = $source->value('email'))) {
      return;
    }

    $wrapped_contact = $this->newOrExistingContactByEmail($email, $contact_type);
    $isNewOrUpdated = empty($wrapped_contact->contact_id);

    foreach ($this->mappings as $mapper) {
      $isNewOrUpdated = $mapper->import($source, $wrapped_contact, TRUE) || $isNewOrUpdated;
    }

    if ($isNewOrUpdated) {
      $wrapped_contact->save();
    }

    return $wrapped_contact->value();
  }

  public function newOrExistingContactByEmail($email, $contact_type = 'contact') {
    if (!($contact = Contact::byEmail($email))) {
      $contact = new Contact(array('type' => $contact_type));
      $contact->setEmail($email, 1, 0);
    }
    return $contact->wrap();
  }
}

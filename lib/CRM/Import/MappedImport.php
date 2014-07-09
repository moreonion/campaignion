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

  public function import(SourceInterface $source, $wrapped_contact) {
    if (!($email = $source->value('email'))) {
      return;
    }
    $isNewOrUpdated = empty($wrapped_contact->contact_id);
    foreach ($this->mappings as $mapper) {
      $isNewOrUpdated = $mapper->import($source, $wrapped_contact, TRUE) || $isNewOrUpdated;
    }
    $gender_salutation_mapping = array('f' => 'mrs', 'm' => 'mr',);
    $gender     = $source->value('gender');
    $salutation = $source->value('salutation');
    if ($wrapped_contact->__isset('field_salutation') && !empty($gender) && empty($salutation) && isset($gender_salutation_mapping[$gender])) {
      $wrapped_contact->field_salutation->set($gender_salutation_mapping[$source->value('gender')]);
      $isNewOrUpdated = TRUE;
    }
    elseif ($wrapped_contact->__isset('field_gender') && empty($gender) && !empty($salutation)) {
      $gender_salutation_mapping = array_flip($gender_salutation_mapping);
      if (isset($gender_salutation_mapping[$salutation])) {
        $wrapped_contact->field_gender->set($gender_salutation_mapping[$source->value('salutation')]);
        $isNewOrUpdated = TRUE;
      }
    }
    $first_name = $wrapped_contact->first_name->value();
    if ($first_name !== ucwords(strtolower($first_name))) {
      $wrapped_contact->first_name->set(ucwords(strtolower($first_name)));
      $isNewOrUpdated = TRUE;
    }
    $last_name = $wrapped_contact->last_name->value();
    if ($last_name !== ucwords(strtolower($last_name))) {
      $wrapped_contact->last_name->set(ucwords(strtolower($last_name)));
      $isNewOrUpdated = TRUE;
    }

    return $isNewOrUpdated;
  }

  public function getContact(SourceInterface $source, $contact_type = 'contact') {
    if ($email = $source->value('email')) {
      return $this->newOrExistingContactByEmail($email, $contact_type);
    }
  }

  public function newOrExistingContactByEmail($email, $contact_type = 'contact') {
    if (!($contact = Contact::byEmail($email))) {
      $contact = new Contact(array('type' => $contact_type));
      $contact->setEmail($email, 1, 0);
    }
    return $contact->wrap();
  }
}

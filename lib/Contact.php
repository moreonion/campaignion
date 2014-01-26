<?php

namespace Drupal\campaignion;

class Contact extends \RedhenContact {
  public function __construct($values = array()) {
    $objValues = array();
    if (is_object($values)) {
      $objValues = $values;
      $values = array();
    }
    if (!isset($values['type'])) {
      $values['type'] = variable_get('campaignion_contact_type_supporter', 'contact');
    }
    parent::__construct($values);
    foreach ($objValues as $key => $value) {
      $this->$key = $value;
    }
  }

  public static function idFromSubmission($node, $submission) {
    $s = new \Drupal\little_helpers\WebformSubmission($node, $submission);
    if ($email = $s->valueByKey('email')) {
      $first_name = $s->valueByKey('first_name');
      $last_name  = $s->valueByKey('last_name');
      return static::idFromBasicData($email, $first_name, $last_name);
    } else {
      throw new \Exception("Can't create contact without email address.");
    }
  }

  public static function idByEmail($email) {
    $sql = <<<SQL
SELECT entity_id
FROM field_data_redhen_contact_email
WHERE redhen_contact_email_value = :email
SQL;
    return db_query($sql, array(':email' => $email))->fetchField();
  }

  public static function idFromBasicData($email, $first_name = '', $last_name = '') {
    if ($contact_id = static::idByEmail($email)) {
      return $contact_id;
    }
    return static::createContactByBasicData($email, $first_name, $last_name)->contact_id;
  }

  public static function createContactByBasicData($email, $first_name = '', $last_name = '') {
    $contact = new static();
    $contact->setEmail($email);
    $contact->first_name = $first_name;
    $contact->last_name = $last_name;
    $contact->save();
    return $contact;
  }

  public static function byEmail($email) {
    if ($id = self::idByEmail($email)) {
      return new static(redhen_contact_load($id));
    }
  }

  public function wrap() {
    return entity_metadata_wrapper('redhen_contact', $this);
  }
}

<?php

namespace Drupal\campaignion;

class Contact {
  public static function idFromSubmission($node, $submission, $type = 'contact') {
    $s = new \Drupal\little_helpers\WebformSubmission($node, $submission);
    if ($email = $s->valueByKey('email')) {
      $first_name = $s->valueByKey('first_name');
      $last_name  = $s->valueByKey('last_name');
      return static::idFromBasicData($email, $first_name, $last_name, $type);
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

  public static function idFromBasicData($email, $first_name = '', $last_name = '', $type = 'contact') {
    if ($contact_id = static::idByEmail($email)) {
      return $contact_id;
    }
    return static::createContactByBasicData($type, $email, $first_name, $last_name)->contact_id;
  }

  public static function createContactByBasicData($type, $email, $first_name = '', $last_name = '') {
    $contact = redhen_contact_create(array(
      'type' => $type,
    ));
    $contact->setEmail($email);
    $contact->first_name = $first_name;
    $contact->last_name = $last_name;
    $contact->save();
    return $contact;
  }
}

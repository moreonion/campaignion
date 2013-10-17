<?php

namespace Drupal\campaignion;

class Contact {
  public static function idFromSubmission($node, $submission, $type = 'contact') {
    $s = new \Drupal\little_helpers\WebformSubmission($node, $submission);
    if ($email = $s->valueByKey('email')) {
      $sql = <<<SQL
SELECT entity_id
FROM field_data_redhen_contact_email
WHERE redhen_contact_email_value = :email
SQL;

      $contact_id = db_query($sql, array(':email' => $email))->fetchField();
      if (!$contact_id) {
        $contact = static::createContactByEmail($type, $email);
        return $contact->contact_id;
      } else {
        return $contact_id;
      }
    } else {
      throw new \Exception("Can't create contact without email address.");
    }
  }

  public static function createContactByEmail($type, $email) {
    $contact = redhen_contact_create(array(
      'type' => $type,
    ));
    $contact->setEmail($email);
    $contact->save();
    return $contact;
  }
}

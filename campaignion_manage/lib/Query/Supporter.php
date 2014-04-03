<?php

namespace Drupal\campaignion_manage\Query;

class Supporter extends Base {
  public function __construct() {
    $query = db_select('redhen_contact', 'r');
    $query->innerJoin('field_data_redhen_contact_email', 'e', 'e.entity_id = r.contact_id');
    $query->fields('r', array('contact_id', 'first_name', 'middle_name', 'last_name'))
      ->fields('e', array('redhen_contact_email_value'))
      ->orderBy('r.updated', 'DESC');
    
    $this->query = $query;
  }
}

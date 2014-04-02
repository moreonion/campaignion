<?php

namespace Drupal\campaignion_manage;

class TemplateQuery extends ContentQuery {

  public function __construct() {
    parent::__construct();
    $this->query->innerJoin('field_data_action_template', 'fat', 'fat.entity_id = n.nid');
    $this->query->condition('fat.action_template_value', 1);
  }
}
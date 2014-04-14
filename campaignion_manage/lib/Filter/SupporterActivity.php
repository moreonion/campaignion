<?php

namespace Drupal\campaignion_manage\Filter;

class SupporterActivity extends Base implements FilterInterface {
  protected $query;

  public function __construct(\SelectQueryInterface $query) {
    $this->query = $query;
  }

  protected function getOptions() {
    $query = clone $this->query;
    $query->innerJoin('campaignion_activity', 'act', "r.contact_id = act.contact_id");
    $fields =& $query->getFields();
    $fields = array();
    $query->condition('act.type', array('redhen_contact_create', 'redhen_contact_edit'), 'IN');
    $query->fields('act', array('type'));
    $query->groupBy('act.type');

    $activities_in_use = $query->execute()->fetchAllKeyed(0,0);

    $query = clone $this->query;
    $query->innerJoin('campaignion_activity', 'act', "r.contact_id = act.contact_id");
    $query->innerJoin('campaignion_activity_webform', 'wact', "act.activity_id = wact.activity_id");
    $query->innerJoin('node', 'n', "wact.nid = n.nid");
    $fields =& $query->getFields();
    $fields = array();
    $query->fields('n', array('type'));
    $query->groupBy('n.type');

    $activities_in_use += $query->execute()->fetchAllKeyed(0,0);

    $available_activities = array(
      'redhen_contact_create' => t('Contact created'),
      'redhen_contact_edit' => t('Contact edited'),
      'petition' => t('Petition'),
      'donation' => t('Donation'),
      'email_protest' => t('Email Protest'),
      'webform' => t('Flexible Form'),
    );

    return array_intersect_key($available_activities, $activities_in_use);
  }

  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['frequency'] = array(
      '#type'          => 'select',
      '#title'         => t('Activity frequency'),
      '#options'       => array('any' => t('Any frequency'), 'how_many' => t('How many times?')),
      '#default_value' => isset($values['frequency']) ? $values['frequency'] : NULL,
    );
    $form['how_many_op'] = array(
      '#type'          => 'select',
      '#title'         => t('Frequency operator'),
      '#options'       => array('=' => t('Exactly'), '>' => t('More than'), '<' => t('Less than')),
      '#default_value' => isset($values['how_many_op']) ? $values['how_many_op'] : NULL,
    );
    $form['how_many_nr'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Specify number of times'),
      '#size'          => 10,
      '#maxlength'     => 10,
      '#default_value' => isset($values['how_many_nr']) ? $values['how_many_nr'] : NULL,
    );
    $form['activity'] = array(
      '#type'          => 'select',
      '#title'         => t('Activity'),
      '#options'       => $this->getOptions(),
      '#default_value' => isset($values['activity']) ? $values['activity'] : NULL,
    );
    $form['date_range'] = array(
      '#type'          => 'select',
      '#title'         => t('Date range'),
      '#options'       => array('none' => t('-- None --'), 'range' => t('Date range'), 'before' => t('Before'), 'after' => t('After')),
      '#default_value' => isset($values['date_range']) ? $values['date_range'] : NULL,
    );
    $form['date_after'] = array(
      '#type'          => 'textfield',
      '#title'         => t('After'),
      '#size'          => 10,
      '#maxlength'     => 10,
      '#default_value' => isset($values['date_after']) ? $values['date_after'] : NULL,
    );
    $form['date_before'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Before'),
      '#size'          => 10,
      '#maxlength'     => 10,
      '#default_value' => isset($values['date_before']) ? $values['date_before'] : NULL,
    );
  }

  public function title() { return t('Activity'); }

  public function apply($query, array $values) {
    $inner = clone $query;
    $inner->innerJoin('campaignion_activity', 'act', "r.contact_id = act.contact_id");
    $fields =& $inner->getFields();
    $fields = array();
    $inner->fields('r', array('contact_id'));
    $inner->groupBy('r.contact_id');

    if ($values['activity'] == 'redhen_contact_create' || $values['activity'] == 'redhen_contact_edit') {
      $inner->condition('act.type', $values['activity']);
    }
    else {
      $inner->innerJoin('campaignion_activity_webform', 'wact', "act.activity_id = wact.activity_id");
      $inner->innerJoin('node', 'n', "wact.nid = n.nid");
      $inner->condition('n.type', $values['activity']);
    }

    $query->condition('r.contact_id', $inner, 'IN');
  }

  public function isApplicable($current) { return empty($current) && count($this->getOptions()) > 0; }

  public function defaults() {
    return array(
      'frequency' => 'any',
      'how_many_op' => '=',
      'how_many_nr' => '1',
      'activity' => reset($this->getOptions()),
      'date_range' => 'none',
      'date_after' => '',
      'date_before' => '',
      );
  }
}

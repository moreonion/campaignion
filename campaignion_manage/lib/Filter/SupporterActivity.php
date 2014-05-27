<?php

namespace Drupal\campaignion_manage\Filter;

class SupporterActivity extends Base implements FilterInterface {
  protected $query;

  public function __construct(\SelectQueryInterface $query) {
    $this->query = $query;
  }

  protected function getOptions() {
    $activities_in_use = array('any_activity' => t('Any activity'));

    $query = db_select('campaignion_activity', 'act');
    $query->condition('act.type', 'redhen_contact_create');
    $query->fields('act', array('type'));
    $query->groupBy('act.type');

    $activities_in_use += $query->execute()->fetchAllKeyed(0,0);

    $query = db_select('campaignion_activity', 'act');
    $query->innerJoin('campaignion_activity_webform', 'wact', "act.activity_id = wact.activity_id");
    $query->innerJoin('node', 'n', "wact.nid = n.nid");
    $query->fields('n', array('nid', 'type', 'title'));
    $query->where('n.tnid = 0 OR n.tnid = n.nid');

    $options = array();
    foreach ($query->execute()->fetchAllAssoc('nid') as $nid => $action) {
      $activities_in_use[$action->type] = $action->type;
      $options['actions'][$action->type][$nid] = $action->title;
    }

    $available_activities = array(
      'any_activity'          => t('Any type'),
      'redhen_contact_create' => t('Contact created'),
      'petition'              => t('Petition'),
      'donation'              => t('Donation'),
      'email_protest'         => t('Email Protest'),
      'webform'               => t('Flexible Form'),
    );
    $options['activity_types'] = array_intersect_key($available_activities, $activities_in_use);

    return $options;
  }

  public function formElement(array &$form, array &$form_state, array &$values) {
    $frequency_id  = drupal_html_id('activity-frequency');
    $date_range_id = drupal_html_id('activity-date-range');
    $activity_type_id = drupal_html_id('activity-type');
    $options = $this->getOptions();
    $form['frequency'] = array(
      '#type'          => 'select',
      '#title'         => t('Activity'),
      '#attributes'    => array('id' => $frequency_id),
      '#options'       => array('any' => t('Any frequency'), 'how_many' => t('How many times?')),
      '#default_value' => isset($values['frequency']) ? $values['frequency'] : NULL,
    );
    $form['how_many_op'] = array(
      '#type'          => 'select',
      '#options'       => array('=' => t('Exactly'), '>' => t('More than'), '<' => t('Less than')),
      '#states'        => array('visible' => array('#' . $frequency_id => array('value' => 'how_many'))),
      '#default_value' => isset($values['how_many_op']) ? $values['how_many_op'] : NULL,
    );
    $form['how_many_nr'] = array(
      '#type'          => 'textfield',
      '#size'          => 10,
      '#maxlength'     => 10,
      '#states'        => array('visible' => array('#' . $frequency_id => array('value' => 'how_many'))),
      '#default_value' => isset($values['how_many_nr']) ? $values['how_many_nr'] : NULL,
      '#element_validate' => array('campaignion_manage_activity_how_many_validate'),
    );
    $form['activity'] = array(
      '#type'          => 'select',
      '#attributes'    => array('id' => $activity_type_id),
      '#options'       => $options['activity_types'],
      '#default_value' => isset($values['activity']) ? $values['activity'] : NULL,
    );
    $activity_types = array(
      'donation'      => t('Donation Action'),
      'email_protest' => t('Email Protest Actions'),
      'petition'      => t('Petition Actions'),
      'webform'       => t('Flexible Form Actions'),
    );
    foreach ($activity_types as $type => $type_name) {
      if (!empty($options['actions'][$type])) {
        $form['action_' . $type] = array(
          '#type'          => 'select',
          '#options'       => array('no_specific' => t('No specific action')) + $options['actions'][$type],
          '#states'        => array('visible' => array('#' . $activity_type_id => array('value' => $type))),
          '#default_value' => isset($values['action_' . $type]) ? $values['action_' . $type] : NULL,
        );
      }
    }
    $form['date_range'] = array(
      '#type'          => 'select',
      '#attributes'    => array('id' => $date_range_id),
      '#options'       => array('all' => t('Any time'), 'range' => t('Date range'), 'to' => t('to'), 'from' => t('from')),
      '#default_value' => isset($values['date_range']) ? $values['date_range'] : NULL,
    );
    $form['date_from'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('from'),
      '#description'   => t('Specify a date in the format YYYY/MM/DD'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'from'))),
      '#default_value' => isset($values['date_from']) ? $values['date_from'] : NULL,
      '#attributes'    => array('class' => array('campaignion-manage-date')),
    );
    $form['date_to'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('to'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'to'))),
      '#default_value' => isset($values['date_to']) ? $values['date_to'] : NULL,
      '#attributes'    => array('class' => array('campaignion-manage-date')),
    );
    $form['date_range_from'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('from'),
      '#description'   => t('Specify a date in the format YYYY/MM/DD'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'range'))),
      '#default_value' => isset($values['date_from']) ? $values['date_from'] : NULL,
      '#attributes'    => array('class' => array('campaignion-manage-date')),
    );
    $form['date_range_to'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('to'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'range'))),
      '#default_value' => isset($values['date_to']) ? $values['date_to'] : NULL,
      '#attributes'    => array('class' => array('campaignion-manage-date')),
    );
  }

  public function title() { return t('Activity'); }

  public function apply($query, array $values) {
    $inner = db_select('redhen_contact', 'r');
    $inner->innerJoin('campaignion_activity', 'act', "r.contact_id = act.contact_id");
    // "RedHen contact was edited" activities are never shown
    $inner->condition('act.type', 'redhen_contact_edit', '!=');
    $fields =& $inner->getFields();
    $fields = array();
    $inner->fields('r', array('contact_id'));

    if ($values['activity'] === 'redhen_contact_create') {
      $inner->condition('act.type', 'redhen_contact_create');
    }
    elseif ($values['activity'] !== 'any_activity') {
      $inner->innerJoin('campaignion_activity_webform', 'wact', "act.activity_id = wact.activity_id");
      $inner->innerJoin('node', 'n', "wact.nid = n.nid");
      if ($values['action_' . $values['activity']] !== 'no_specific') {
        $inner->where('n.nid = :nid OR n.tnid = :nid', array(':nid' => $values['action_' . $values['activity']]));
      }
      else {
        $inner->condition('n.type', $values['activity']);
      }
    }

    if ($values['frequency'] === 'how_many') {
      if ($values['activity'] === 'any_activity') {
        // when the user selects any activity but wants to filter for number of
        // activities we don't want to include "RedHen contact was created" activities
        $inner->condition('act.type', 'redhen_contact_create', '!=');
      }
      $inner->addExpression('COUNT(r.contact_id)', 'count_activities');
      $inner->havingCondition('count_activities', $values['how_many_nr'], $values['how_many_op']);
    }

    switch ($values['date_range']) {
      case 'range':
        $date_range = array(strtotime($values['date_range_from']), strtotime($values['date_range_to']));
        $inner->condition('act.created', $date_range, 'BETWEEN');
        break;
      case 'to':
        $to = strtotime($values['date_to']);
        $inner->condition('act.created', $to, '<');
        break;
      case 'from':
        $from  = strtotime($values['date_from']);
        $inner->condition('act.created', $from, '>');
        break;
    }
    $query->condition('r.contact_id', $inner, 'IN');
  }

  public function isApplicable($current) {
    $options = $this->getOptions();
    return empty($current) && count($options['activity_types']) > 1;
  }

  public function defaults() {
    $options = $this->getOptions();
    return array(
      'frequency'   => 'any',
      'how_many_op' => '=',
      'how_many_nr' => '1',
      'activity'    => key($options['activity_types']),
      'date_range'  => 'all',
      'date_after'  => '',
      'date_before' => '',
    );
  }
}

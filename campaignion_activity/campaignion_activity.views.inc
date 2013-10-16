<?php
/**
 * @file
 *
 * @author    Matthias Weiss <matthias@more-onion.com>
 * @copyright Copyright (c) 2013 copyright
 */

/**
 * Implements hook_views_data().
 */
function campaignion_activity_views_data() {

  $data['campaignion_activity']['table']['group'] = t('Activity');

  // Advertise this table as a possible base table
  $data['campaignion_activity']['table']['base'] = array(
    'field'    => 'activity_id',
    'title'    => t('Activity'),
    'defaults' => array(
      'field' => 'contact_id',
    ),
  );

  $data['campaignion_activity']['table']['default_relationship'] = array(
    'redhen_contact' => array(
      'table' => 'redhen_contact',
      'field' => 'contact_id',
    ),
  );

  // ----------------------------------------------------------------
  // fields

  // contact_id
  $data['campaignion_activity']['contact_id'] = array(
    'title' => t('Contact ID'),
    'help'  => t('The ID of the RedHen contact.'),
    'field' => array(
      'handler'        => 'views_handler_field',
      'click sortable' => TRUE,
    ),
    'relationship' => array(
      'title'      => t('RedHen Contact'),
      'help'       => t('The RedHen contact which is connected to this activity.'),
      'base'       => 'redhen_contact',
      'base field' => 'contact_id',
      'handler'    => 'views_handler_relationship',
      'label'      => t('RedHen Contact'),
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

  // activity type
  $data['campaignion_activity']['type'] = array(
    'title' => t('Activity Type'),
    'help'  => t('The type of activity.'),
    'field' => array(
      'handler'        => 'views_handler_field',
      'click sortable' => TRUE,
      ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_activity_type',
    ),
  );

  // created field
  $data['campaignion_activity']['created'] = array(
    'title' => t('Activity Date'),
    'help'  => t('The date/time this activity happened.'),
    'field' => array(
      'handler'        => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_date',
    ),
  );

  return $data;
}
<?php
/**
 * @file
 * campaignion_views_interface.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function campaignion_views_interface_views_default_views() {
  $export = array();

  $view = new view();
  $view->name = 'manage_media';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'file_managed';
  $view->human_name = 'Manage media';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Manage content and navigation';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['exposed_form']['options']['autosubmit'] = TRUE;
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '10';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'views_bulk_operations' => 'views_bulk_operations',
    'filename' => 'filename',
    'timestamp' => 'timestamp',
    'name' => 'name',
    'rendered_entity' => 'rendered_entity',
    'title' => 'title',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'views_bulk_operations' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'filename' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'timestamp' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'name' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'rendered_entity' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'title' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* No results behavior: Global: Text area */
  $handler->display->display_options['empty']['area']['id'] = 'area';
  $handler->display->display_options['empty']['area']['table'] = 'views';
  $handler->display->display_options['empty']['area']['field'] = 'area';
  $handler->display->display_options['empty']['area']['empty'] = TRUE;
  $handler->display->display_options['empty']['area']['content'] = 'There is nothing to display. Change your search filters or add more content!';
  $handler->display->display_options['empty']['area']['format'] = 'full_html_with_editor';
  /* Relationship: File: User who uploaded */
  $handler->display->display_options['relationships']['uid']['id'] = 'uid';
  $handler->display->display_options['relationships']['uid']['table'] = 'file_managed';
  $handler->display->display_options['relationships']['uid']['field'] = 'uid';
  /* Relationship: File Usage: Content */
  $handler->display->display_options['relationships']['file_to_node']['id'] = 'file_to_node';
  $handler->display->display_options['relationships']['file_to_node']['table'] = 'file_usage';
  $handler->display->display_options['relationships']['file_to_node']['field'] = 'file_to_node';
  /* Field: Bulk operations: File */
  $handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['table'] = 'file_managed';
  $handler->display->display_options['fields']['views_bulk_operations']['field'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['label'] = '';
  $handler->display->display_options['fields']['views_bulk_operations']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['display_type'] = '1';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['enable_select_all_pages'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['force_single'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['entity_load_capacity'] = '10';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_operations'] = array(
    'action::views_bulk_operations_archive_action' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'settings' => array(
        'scheme' => 'public',
        'temporary' => 1,
      ),
    ),
    'action::views_bulk_operations_delete_item' => array(
      'selected' => 0,
      'postpone_processing' => 0,
      'skip_confirmation' => 1,
      'override_label' => 1,
      'label' => '',
    ),
    'action::views_bulk_operations_script_action' => array(
      'selected' => 0,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::views_bulk_operations_modify_action' => array(
      'selected' => 0,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'settings' => array(
        'show_all_tokens' => 1,
        'display_values' => array(
          '_all_' => '_all_',
        ),
      ),
    ),
    'action::views_bulk_operations_argument_selector_action' => array(
      'selected' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'settings' => array(
        'url' => '',
      ),
    ),
    'action::system_send_email_action' => array(
      'selected' => 0,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::redhen_fields_email_action' => array(
      'selected' => 0,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
  );
  /* Field: File: Name */
  $handler->display->display_options['fields']['filename']['id'] = 'filename';
  $handler->display->display_options['fields']['filename']['table'] = 'file_managed';
  $handler->display->display_options['fields']['filename']['field'] = 'filename';
  $handler->display->display_options['fields']['filename']['label'] = 'File name';
  $handler->display->display_options['fields']['filename']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['filename']['alter']['ellipsis'] = FALSE;
  /* Field: File: Upload date */
  $handler->display->display_options['fields']['timestamp']['id'] = 'timestamp';
  $handler->display->display_options['fields']['timestamp']['table'] = 'file_managed';
  $handler->display->display_options['fields']['timestamp']['field'] = 'timestamp';
  $handler->display->display_options['fields']['timestamp']['date_format'] = 'short';
  /* Field: User: Name */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'users';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  $handler->display->display_options['fields']['name']['relationship'] = 'uid';
  $handler->display->display_options['fields']['name']['label'] = 'Author';
  /* Field: Content: Rendered Node */
  $handler->display->display_options['fields']['rendered_entity']['id'] = 'rendered_entity';
  $handler->display->display_options['fields']['rendered_entity']['table'] = 'views_entity_node';
  $handler->display->display_options['fields']['rendered_entity']['field'] = 'rendered_entity';
  $handler->display->display_options['fields']['rendered_entity']['relationship'] = 'file_to_node';
  $handler->display->display_options['fields']['rendered_entity']['label'] = 'Thumbnail';
  $handler->display->display_options['fields']['rendered_entity']['exclude'] = TRUE;
  $handler->display->display_options['fields']['rendered_entity']['link_to_entity'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['display'] = 'view';
  $handler->display->display_options['fields']['rendered_entity']['view_mode'] = 'teaser';
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['relationship'] = 'file_to_node';
  $handler->display->display_options['fields']['title']['label'] = 'Page ';
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['relationship'] = 'file_to_node';
  $handler->display->display_options['filters']['type']['group'] = 1;
  $handler->display->display_options['filters']['type']['exposed'] = TRUE;
  $handler->display->display_options['filters']['type']['expose']['operator_id'] = 'type_op';
  $handler->display->display_options['filters']['type']['expose']['label'] = 'Type';
  $handler->display->display_options['filters']['type']['expose']['description'] = 'filter media used on specific content types';
  $handler->display->display_options['filters']['type']['expose']['operator'] = 'type_op';
  $handler->display->display_options['filters']['type']['expose']['identifier'] = 'type';
  $handler->display->display_options['filters']['type']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
  );
  /* Filter criterion: Content: Related campaign (field_reference_to_campaign) */
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['id'] = 'field_reference_to_campaign_nid';
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['table'] = 'field_data_field_reference_to_campaign';
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['field'] = 'field_reference_to_campaign_nid';
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['relationship'] = 'file_to_node';
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['group'] = 1;
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['expose']['operator_id'] = 'field_reference_to_campaign_nid_op';
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['expose']['label'] = 'Used in campaign';
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['expose']['operator'] = 'field_reference_to_campaign_nid_op';
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['expose']['identifier'] = 'field_reference_to_campaign_nid';
  $handler->display->display_options['filters']['field_reference_to_campaign_nid']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
  );
  /* Filter criterion: File: Mime type */
  $handler->display->display_options['filters']['filemime']['id'] = 'filemime';
  $handler->display->display_options['filters']['filemime']['table'] = 'file_managed';
  $handler->display->display_options['filters']['filemime']['field'] = 'filemime';
  $handler->display->display_options['filters']['filemime']['group'] = 1;
  $handler->display->display_options['filters']['filemime']['exposed'] = TRUE;
  $handler->display->display_options['filters']['filemime']['expose']['operator_id'] = 'filemime_op';
  $handler->display->display_options['filters']['filemime']['expose']['label'] = 'File type';
  $handler->display->display_options['filters']['filemime']['expose']['description'] = 'filter by image, audio or file extension';
  $handler->display->display_options['filters']['filemime']['expose']['operator'] = 'filemime_op';
  $handler->display->display_options['filters']['filemime']['expose']['identifier'] = 'filemime';
  $handler->display->display_options['filters']['filemime']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
  );

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['defaults']['title'] = FALSE;
  $handler->display->display_options['title'] = 'Manage media';
  $handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
  $handler->display->display_options['path'] = 'admin/manage/media';
  $translatables['manage_media'] = array(
    t('Master'),
    t('Manage content and navigation'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Items per page'),
    t('- All -'),
    t('Offset'),
    t('« first'),
    t('‹ previous'),
    t('next ›'),
    t('last »'),
    t('There is nothing to display. Change your search filters or add more content!'),
    t('User who uploaded'),
    t('Content'),
    t('File name'),
    t('Upload date'),
    t('Author'),
    t('Thumbnail'),
    t('Page '),
    t('Type'),
    t('filter media used on specific content types'),
    t('Used in campaign'),
    t('File type'),
    t('filter by image, audio or file extension'),
    t('Page'),
    t('Manage media'),
  );
  $export['manage_media'] = $view;

  $view = new view();
  $view->name = 'supporters';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'redhen_contact';
  $view->human_name = 'supporters';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Supporters';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['group_by'] = TRUE;
  $handler->display->display_options['access']['type'] = 'role';
  $handler->display->display_options['access']['role'] = array(
    3 => '3',
    4 => '4',
  );
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['exposed_form']['options']['autosubmit'] = TRUE;
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '20';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'views_bulk_operations' => 'views_bulk_operations',
    'first_name' => 'first_name',
    'last_name' => 'last_name',
    'field_address' => 'field_address',
    'redhen_contact_email' => 'redhen_contact_email',
    'field_tags' => 'field_tags',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'views_bulk_operations' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'first_name' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'last_name' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_address' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'redhen_contact_email' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_tags' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* No results behavior: Global: Text area */
  $handler->display->display_options['empty']['area']['id'] = 'area';
  $handler->display->display_options['empty']['area']['table'] = 'views';
  $handler->display->display_options['empty']['area']['field'] = 'area';
  $handler->display->display_options['empty']['area']['empty'] = TRUE;
  $handler->display->display_options['empty']['area']['content'] = 'There is nothing to display. Change your search filters or add more supporters!';
  $handler->display->display_options['empty']['area']['format'] = 'full_html_with_editor';
  /* Relationship: Activity Message referencing Contact */
  $handler->display->display_options['relationships']['reverse_message_redhen_contact_message']['id'] = 'reverse_message_redhen_contact_message';
  $handler->display->display_options['relationships']['reverse_message_redhen_contact_message']['table'] = 'redhen_contact';
  $handler->display->display_options['relationships']['reverse_message_redhen_contact_message']['field'] = 'reverse_message_redhen_contact_message';
  $handler->display->display_options['relationships']['reverse_message_redhen_contact_message']['ui_name'] = 'Activity Message referencing Contact';
  $handler->display->display_options['relationships']['reverse_message_redhen_contact_message']['label'] = 'Activity Message referencing Contact';
  /* Field: Bulk operations: Contact */
  $handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['table'] = 'redhen_contact';
  $handler->display->display_options['fields']['views_bulk_operations']['field'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['display_type'] = '1';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['enable_select_all_pages'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['force_single'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['entity_load_capacity'] = '10';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_operations'] = array(
    'action::views_bulk_operations_delete_item' => array(
      'selected' => 1,
      'skip_confirmation' => 0,
      'override_label' => 1,
      'label' => 'Delete contact',
      'postpone_processing' => 0,
    ),
    'action::system_message_action' => array(
      'selected' => 1,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'postpone_processing' => 0,
    ),
    'action::views_bulk_operations_script_action' => array(
      'selected' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'postpone_processing' => 0,
    ),
    'action::views_bulk_operations_modify_action' => array(
      'selected' => 1,
      'skip_confirmation' => 0,
      'override_label' => 1,
      'label' => 'Add tag',
      'settings' => array(
        'show_all_tokens' => 1,
        'display_values' => array(
          'contact::field_tags' => 'contact::field_tags',
        ),
      ),
      'postpone_processing' => 0,
    ),
    'action::views_bulk_operations_argument_selector_action' => array(
      'selected' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'settings' => array(
        'url' => '',
      ),
    ),
    'action::system_goto_action' => array(
      'selected' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'postpone_processing' => 0,
    ),
    'action::system_send_email_action' => array(
      'selected' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'postpone_processing' => 0,
    ),
    'action::redhen_fields_email_action' => array(
      'selected' => 1,
      'skip_confirmation' => 0,
      'override_label' => 1,
      'label' => 'Send Email',
      'postpone_processing' => 0,
    ),
  );
  /* Field: Contact: Contact ID */
  $handler->display->display_options['fields']['contact_id']['id'] = 'contact_id';
  $handler->display->display_options['fields']['contact_id']['table'] = 'redhen_contact';
  $handler->display->display_options['fields']['contact_id']['field'] = 'contact_id';
  $handler->display->display_options['fields']['contact_id']['label'] = '';
  $handler->display->display_options['fields']['contact_id']['exclude'] = TRUE;
  $handler->display->display_options['fields']['contact_id']['element_label_colon'] = FALSE;
  /* Field: Contact: First name */
  $handler->display->display_options['fields']['first_name']['id'] = 'first_name';
  $handler->display->display_options['fields']['first_name']['table'] = 'redhen_contact';
  $handler->display->display_options['fields']['first_name']['field'] = 'first_name';
  $handler->display->display_options['fields']['first_name']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['first_name']['alter']['path'] = 'redhen/contact/[contact_id]';
  /* Field: Contact: Last name */
  $handler->display->display_options['fields']['last_name']['id'] = 'last_name';
  $handler->display->display_options['fields']['last_name']['table'] = 'redhen_contact';
  $handler->display->display_options['fields']['last_name']['field'] = 'last_name';
  $handler->display->display_options['fields']['last_name']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['last_name']['alter']['path'] = 'redhen/contact/[contact_id]';
  /* Field: Contact: Adresse */
  $handler->display->display_options['fields']['field_address']['id'] = 'field_address';
  $handler->display->display_options['fields']['field_address']['table'] = 'field_data_field_address';
  $handler->display->display_options['fields']['field_address']['field'] = 'field_address';
  $handler->display->display_options['fields']['field_address']['click_sort_column'] = 'country';
  $handler->display->display_options['fields']['field_address']['settings'] = array(
    'use_widget_handlers' => 1,
    'format_handlers' => array(
      'address' => 'address',
    ),
  );
  $handler->display->display_options['fields']['field_address']['group_column'] = 'entity_id';
  $handler->display->display_options['fields']['field_address']['group_columns'] = array(
    'country' => 'country',
    'administrative_area' => 'administrative_area',
    'locality' => 'locality',
    'postal_code' => 'postal_code',
    'thoroughfare' => 'thoroughfare',
    'premise' => 'premise',
  );
  $handler->display->display_options['fields']['field_address']['delta_limit'] = '1';
  $handler->display->display_options['fields']['field_address']['delta_offset'] = '0';
  $handler->display->display_options['fields']['field_address']['multi_type'] = 'ol';
  /* Field: Contact: E-Mail */
  $handler->display->display_options['fields']['redhen_contact_email']['id'] = 'redhen_contact_email';
  $handler->display->display_options['fields']['redhen_contact_email']['table'] = 'field_data_redhen_contact_email';
  $handler->display->display_options['fields']['redhen_contact_email']['field'] = 'redhen_contact_email';
  $handler->display->display_options['fields']['redhen_contact_email']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['redhen_contact_email']['alter']['text'] = '[redhen_contact_email-value] ';
  $handler->display->display_options['fields']['redhen_contact_email']['hide_alter_empty'] = FALSE;
  $handler->display->display_options['fields']['redhen_contact_email']['group_columns'] = array(
    'entity_id' => 'entity_id',
    'label_id' => 'label_id',
  );
  $handler->display->display_options['fields']['redhen_contact_email']['delta_offset'] = '0';
  /* Field: Field: Tags */
  $handler->display->display_options['fields']['field_tags']['id'] = 'field_tags';
  $handler->display->display_options['fields']['field_tags']['table'] = 'field_data_field_tags';
  $handler->display->display_options['fields']['field_tags']['field'] = 'field_tags';
  $handler->display->display_options['fields']['field_tags']['delta_offset'] = '0';
  /* Field: Message: Action Node */
  $handler->display->display_options['fields']['message_ae_action_node']['id'] = 'message_ae_action_node';
  $handler->display->display_options['fields']['message_ae_action_node']['table'] = 'field_data_message_ae_action_node';
  $handler->display->display_options['fields']['message_ae_action_node']['field'] = 'message_ae_action_node';
  $handler->display->display_options['fields']['message_ae_action_node']['relationship'] = 'reverse_message_redhen_contact_message';
  $handler->display->display_options['fields']['message_ae_action_node']['label'] = '';
  $handler->display->display_options['fields']['message_ae_action_node']['exclude'] = TRUE;
  $handler->display->display_options['fields']['message_ae_action_node']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['message_ae_action_node']['type'] = 'node_reference_plain';
  $handler->display->display_options['fields']['message_ae_action_node']['group_column'] = 'entity_id';
  /* Sort criterion: Contact: Last name */
  $handler->display->display_options['sorts']['last_name']['id'] = 'last_name';
  $handler->display->display_options['sorts']['last_name']['table'] = 'redhen_contact';
  $handler->display->display_options['sorts']['last_name']['field'] = 'last_name';
  /* Filter criterion: Global: Combine fields filter */
  $handler->display->display_options['filters']['combine']['id'] = 'combine';
  $handler->display->display_options['filters']['combine']['table'] = 'views';
  $handler->display->display_options['filters']['combine']['field'] = 'combine';
  $handler->display->display_options['filters']['combine']['operator'] = 'contains';
  $handler->display->display_options['filters']['combine']['group'] = 1;
  $handler->display->display_options['filters']['combine']['exposed'] = TRUE;
  $handler->display->display_options['filters']['combine']['expose']['operator_id'] = 'combine_op';
  $handler->display->display_options['filters']['combine']['expose']['label'] = 'Search';
  $handler->display->display_options['filters']['combine']['expose']['operator'] = 'combine_op';
  $handler->display->display_options['filters']['combine']['expose']['identifier'] = 'combine';
  $handler->display->display_options['filters']['combine']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
  );
  $handler->display->display_options['filters']['combine']['fields'] = array(
    'first_name' => 'first_name',
    'last_name' => 'last_name',
    'field_address' => 'field_address',
    'redhen_contact_email' => 'redhen_contact_email',
  );
  /* Filter criterion: Field: Tags (field_tags) */
  $handler->display->display_options['filters']['field_tags_tid']['id'] = 'field_tags_tid';
  $handler->display->display_options['filters']['field_tags_tid']['table'] = 'field_data_field_tags';
  $handler->display->display_options['filters']['field_tags_tid']['field'] = 'field_tags_tid';
  $handler->display->display_options['filters']['field_tags_tid']['group'] = 1;
  $handler->display->display_options['filters']['field_tags_tid']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_tags_tid']['expose']['operator_id'] = 'field_tags_tid_op';
  $handler->display->display_options['filters']['field_tags_tid']['expose']['label'] = 'Tag';
  $handler->display->display_options['filters']['field_tags_tid']['expose']['operator'] = 'field_tags_tid_op';
  $handler->display->display_options['filters']['field_tags_tid']['expose']['identifier'] = 'field_tags_tid';
  $handler->display->display_options['filters']['field_tags_tid']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
  );
  $handler->display->display_options['filters']['field_tags_tid']['group_info']['label'] = 'Tags (field_tags)';
  $handler->display->display_options['filters']['field_tags_tid']['group_info']['identifier'] = 'field_tags_tid';
  $handler->display->display_options['filters']['field_tags_tid']['group_info']['remember'] = FALSE;
  $handler->display->display_options['filters']['field_tags_tid']['group_info']['group_items'] = array(
    1 => array(),
    2 => array(),
    3 => array(),
  );
  $handler->display->display_options['filters']['field_tags_tid']['reduce_duplicates'] = TRUE;
  $handler->display->display_options['filters']['field_tags_tid']['type'] = 'select';
  $handler->display->display_options['filters']['field_tags_tid']['vocabulary'] = 'ae_contact_tags_taxonomy';
  /* Filter criterion: Contact: Adresse (field_address:country) */
  $handler->display->display_options['filters']['field_address_country']['id'] = 'field_address_country';
  $handler->display->display_options['filters']['field_address_country']['table'] = 'field_data_field_address';
  $handler->display->display_options['filters']['field_address_country']['field'] = 'field_address_country';
  $handler->display->display_options['filters']['field_address_country']['group'] = 1;
  $handler->display->display_options['filters']['field_address_country']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_address_country']['expose']['operator_id'] = 'field_address_country_op';
  $handler->display->display_options['filters']['field_address_country']['expose']['label'] = 'Country';
  $handler->display->display_options['filters']['field_address_country']['expose']['operator'] = 'field_address_country_op';
  $handler->display->display_options['filters']['field_address_country']['expose']['identifier'] = 'field_address_country';
  $handler->display->display_options['filters']['field_address_country']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
  );
  /* Filter criterion: Message: Action Node (message_ae_action_node) */
  $handler->display->display_options['filters']['message_ae_action_node_nid']['id'] = 'message_ae_action_node_nid';
  $handler->display->display_options['filters']['message_ae_action_node_nid']['table'] = 'field_data_message_ae_action_node';
  $handler->display->display_options['filters']['message_ae_action_node_nid']['field'] = 'message_ae_action_node_nid';
  $handler->display->display_options['filters']['message_ae_action_node_nid']['relationship'] = 'reverse_message_redhen_contact_message';
  $handler->display->display_options['filters']['message_ae_action_node_nid']['exposed'] = TRUE;
  $handler->display->display_options['filters']['message_ae_action_node_nid']['expose']['operator_id'] = 'message_ae_action_node_nid_op';
  $handler->display->display_options['filters']['message_ae_action_node_nid']['expose']['label'] = 'Action';
  $handler->display->display_options['filters']['message_ae_action_node_nid']['expose']['operator'] = 'message_ae_action_node_nid_op';
  $handler->display->display_options['filters']['message_ae_action_node_nid']['expose']['identifier'] = 'message_ae_action_node_nid';
  $handler->display->display_options['filters']['message_ae_action_node_nid']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
  );

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
  $handler->display->display_options['path'] = 'admin/supporters';

  /* Display: Data export */
  $handler = $view->new_display('views_data_export', 'Data export', 'views_data_export_1');
  $handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
  $handler->display->display_options['pager']['type'] = 'some';
  $handler->display->display_options['style_plugin'] = 'views_data_export_csv';
  $handler->display->display_options['path'] = 'admin/supporters/csv';
  $handler->display->display_options['displays'] = array(
    'page' => 'page',
    'default' => 0,
  );
  $translatables['supporters'] = array(
    t('Master'),
    t('Supporters'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Items per page'),
    t('- All -'),
    t('Offset'),
    t('« first'),
    t('‹ previous'),
    t('next ›'),
    t('last »'),
    t('There is nothing to display. Change your search filters or add more supporters!'),
    t('Activity Message referencing Contact'),
    t('Contact'),
    t('.'),
    t(','),
    t('First name'),
    t('Last name'),
    t('Adresse'),
    t('E-Mail'),
    t('[redhen_contact_email-value] '),
    t('Tags'),
    t('Search'),
    t('Tag'),
    t('Tags (field_tags)'),
    t('Country'),
    t('Action'),
    t('Page'),
    t('Data export'),
  );
  $export['supporters'] = $view;

  return $export;
}

<?php

use \Drupal\campaignion_newsletters\NewsletterList;

/**
 * Administration form.
 */
function campaignion_newsletters_admin_settings() {
  $form = array();

  $form['poll'] = array(
    '#type' => 'button',
    '#value' => t('Update Lists and Subscribers from all external sources now!'),
    '#description' => t('Updates run automatically around every hour in the background'),
    '#weight' => 20,
    '#executes_submit_callback' => TRUE,
    '#submit' => array('campaignion_newsletters_admin_poll'),
  );

  // Leave actual implementation to submodules for now.
  return system_settings_form($form);
}

/**
 * Submit callback for the polling button in the admin interface.
 */
function campaignion_newsletters_admin_poll() {
  drupal_set_message(t('Updating newsletter data...'));
  _campaignion_newsletters_poll();
}

/**
 * Implementation of campaignion_newsletters_form_redhen_contact_contact_form_alter().
 */
function _campaignion_newsletters_form_redhen_contact_contact_form_alter(&$form, &$form_state) {
  $mails = array();
  foreach ($form_state['redhen_contact']->allEmail() as $entry) {
    $mails[] = $entry['value'];
  }

  if (!count($mails)) {
    return;
  }

  $options = array();
  $lists = NewsletterList::listAll();
  foreach ($lists as $list) {
    $options[$list->list_id] = $list->title;
  }
  $form_state['newsletter_lists'] = &$lists;

  $result = db_select('campaignion_newsletters_subscriptions', 's')
    ->fields('s', array('list_id', 'email'))
    ->condition('email', $mails, 'IN')
    ->execute();
  $subscriptions = array();
  foreach ($result as $row) {
    $subscriptions[$row->email][] = $row->list_id;
  }

  $fieldset = array(
    '#type' => 'fieldset',
    '#title' => t('Subscriptions'),
    '#collapsible' => FALSE,
    '#weight' => 10,
  );

  foreach ($mails as $mail) {
    $id = drupal_clean_css_identifier($mail);
    $fieldset[$id] = array(
      '#type' => 'checkboxes',
      '#title' => $mail,
      '#options' => $options,
      '#default_value' => isset($subscriptions[$mail]) ? $subscriptions[$mail] : array(),
    );
  }

  $form['newsletters_subscriptions'] = $fieldset;

  array_unshift($form['actions']['submit']['#submit'], 'campaignion_newsletters_redhen_contact_submit');
}

/**
 * Submit handler for redhen_contact_contact_form.
 *
 * Update subscriptions in the database.
 */
function campaignion_newsletters_redhen_contact_submit($form, &$form_state) {
  foreach ($form_state['redhen_contact']->allEmail() as $mail) {
    $id = drupal_clean_css_identifier($mail['value']);
    if (!empty($form_state['values'][$id])) {
      $newsletters = $form_state['values'][$id];
      foreach ($newsletters as $list_id => $subscribed) {
        $list = $form_state['newsletter_lists'][$list_id];
        if ($subscribed) {
          $list->subscribe($mail['value']);
        }
        else {
          $list->unsubscribe($mail['value']);
        }
      }
    }
  }
}

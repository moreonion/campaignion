<?php

namespace Drupal\campaignion_manage\BulkOp;

class SupporterNewsletter implements BulkOpBatchInterface {
  public function title() {
    return 'Subscribe to Newsletter';
  }

  public function helpText() {
    return t('Subscribe the selected supporters to one or more newsletters.');
  }

  public function formElement(&$element, &$form_state) {
    $options = array();
    foreach (\Drupal\campaignion_newsletters\NewsletterList::listAll() as $list) {
      $options[$list->list_id] = $list->title;
    }
    $element['lists'] = array(
      '#type'    => 'checkboxes',
      '#title'   => t('Select one or lists'),
      '#options' => $options,
    );

  }

  public function apply($contact_ids, $values) {
    if (count($contact_ids) <= 0) {
      return;
    }
    $list_ids = array();
    foreach($values['lists'] as $list_id => $value) {
      if ($value) {
        $list_ids[] = $list_id;
      }
    }
    batch_set(array(
      'operations' => array(
        array('campaignion_manage_batch_process', array($this, $contact_ids, $list_ids)),
      ),
      'finished'         => 'campaignion_manage_batch_finished',
      'title'            => t('Subscribing supporters to newsletters ...'),
      'init_message'     => t('Start subscribing supporters to newsletters...'),
      'progress_message' => t('Start subscribing supporters to newsletters...'),
      'error_message'    => t('Encountered an error while subscribing supporters to newsletters.'),
    ));
  }

  public function batchApply($contact_ids, $list_ids, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress']   = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max']        = count($contact_ids);
      $context['results']['bulkOp']     = $this;
    }
    $ids = array_slice(
      $contact_ids,
      $context['sandbox']['progress'],
      min($context['sandbox']['progress'] + 50, $context['sandbox']['max']),
      TRUE
    );
    $contacts = redhen_contact_load_multiple($ids);
    $lists = array_map(
      array('\Drupal\campaignion_newsletters\NewsletterList', 'load'),
      array_values($list_ids));
    foreach ($contacts as $contact_id => $contact) {
      $context['sandbox']['current_id'] = $contact_id;
      foreach ($lists as $list) {
        $list->subscribe($contact->email());
      }
      $context['sandbox']['progress']++;
    }
    $data = array(
      '@current' => $context['sandbox']['progress'],
      '@total'   => $context['sandbox']['max'],
      '@lists'   => count($list_ids)
    );
    $context['message'] = t('Subscribed @current out of @total supporters to @lists newsletters.', $data);

    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  public function batchFinish($success, $results, $operations) {}
}
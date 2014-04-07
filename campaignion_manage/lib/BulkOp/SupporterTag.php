<?php

namespace Drupal\campaignion_manage\BulkOp;

class SupporterTag implements BulkOpBatchInterface {
  public function title() { return t('Tag'); }

  public function helpText() {
    return t('Tag the currently selected supporters with one or more tags.');
  }

  public function formElement(&$element, &$form_state) {
    $options = array();
    foreach (taxonomy_get_tree(taxonomy_vocabulary_machine_name_load('supporter_tags')->vid) as $term) {
      $options[$term->tid] = $term->name;
    }
    $element['tag'] = array(
      '#type'    => 'checkboxes',
      '#title'   => t('Select one or more tags'),
      '#options' => $options,
    );
  }

  public function apply($contact_ids, &$form_state) {
    if (count($contact_ids) > 0) {
      $values = $form_state['values']['bulk-wrapper']['op-wrapper']['op']['tags']['tag'];
      $term_ids = array();
      foreach($values as $tid => $value) {
        if ($value) {
          $term_ids[$tid] = $tid;
        }
      }
      $batch = array(
        'operations' => array(
          array('campaignion_manage_batch_process', array($this, $contact_ids, $term_ids)),
        ),
        'finished'         => 'campaignion_manage_batch_finished',
        'title'            => t('Tagging supporters ...'),
        'init_message'     => t('Start tagging supporters...'),
        'progress_message' => t('Start tagging supporters...'),
        'error_message'    => t('Tagging supporters has encountered an error.'),
      );
      batch_set($batch);
    }
  }

  public function batchApply($contact_ids, $term_ids, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress']   = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max']        = count($contact_ids);
      $context['results']['bulkOp']     = $this;
    }
    $ids = array_slice(
      $contact_ids,
      $context['sandbox']['progress'],
      min($context['sandbox']['progress'] + 100, $context['sandbox']['max']),
      TRUE
    );
    $contacts = redhen_contact_load_multiple($ids);
    foreach ($ids as $contact_id) {
      $context['sandbox']['current_id'] = $contact_id;
      $tids = $term_ids;
      foreach ($contacts[$contact_id]->supporter_tags['und'] as $already_set) {
        if (($i = array_search($already_set['tid'], $tids))) {
          unset($tids[$i]);
        }
      }
      foreach ($tids as $tid) {
        $contacts[$contact_id]->supporter_tags['und'][] = array('tid' => $tid);
      }
      if (!empty($tids)) {
        try {
          $contacts[$contact_id]->save();
        } catch (Exception $e) {
          $context['results']['failed_contacts'][$contact_id] = $e->getMessage();
        }
      }
      $context['message'] = t(
        'Tagged @current out of @total supporters.',
        array(
          '@current' => $context['sandbox']['progress'],
          '@total'   => $context['sandbox']['max']
        )
      );
      $context['sandbox']['progress']++;
    }
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  public function batchFinish($success, $results, $operations) {
    if (isset($results['failed_contacts'])) {
      if (count($results['failed_contacts']) < 11) {
        foreach($results['failed_contacts'] as $contact_id => $error_message) {
          drupal_set_message(t("Contact with ID @contact_id couldn't be tagged: @message", array('@contact_id' => $contact_id, '@message' => $error_message)));
        }
      }
    }
  }
}
<?php

namespace Drupal\campaignion_manage\BulkOp;

class SupporterTag implements BulkOpBatchInterface {
  protected $tag;
  /**
    * @param $tag if set to TRUE, the operation is to add tags
    *    to the supporters; in every other case it means it will
    *    remove the tag
    */
  public function __construct($tag) {
    $this->tag = (bool) $tag;
  }

  public function title() { return $this->tag ? t('Add tag(s)') : t('Delete tag(s)'); }

  public function helpText() {
    return $this->tag ? t('Add one or more tags to the currently selected supporters.') : t('Remove one or more tags to the currently selected supporters.');
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
    if (count($contact_ids) <= 0) {
      return;
    }
    $values = $form_state['values']['bulk-wrapper']['op-wrapper']['op']['tag']['tag'];
    //dpm($values, 'values');
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
    );
    if ($this->tag) {
      $batch += array(
        'title'            => t('Add tags to supporters ...'),
        'init_message'     => t('Start adding tags to supporters...'),
        'progress_message' => t('Start adding tags to supporters...'),
        'error_message'    => t('Encountered an error while adding tags to supporters.'),
      );
    }
    else {
      $batch += array(
        'title'            => t('Delete tags from supporters ...'),
        'init_message'     => t('Start deleting tags from supporters...'),
        'progress_message' => t('Start deleting tags from supporters...'),
        'error_message'    => t('Encountered an error while deleting tags from supporters.'),
      );
    }
    batch_set($batch);
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
    foreach ($contacts as $contact_id => $contact) {
      $context['sandbox']['current_id'] = $contact_id;
      if ($this->tag) {
        $tids = $term_ids;
        // add tags
        foreach ($contact->supporter_tags['und'] as $already_set) {
          unset($tids[$already_set['tid']]);
        }
        foreach ($tids as $tid) {
          $contact->supporter_tags['und'][] = array('tid' => $tid);
        }
      }
      else {
        // delete tags
        $tids = array();
        foreach ($contact->supporter_tags['und'] as $tag_index => $tag) {
          if (isset($term_ids[$tag['tid']])) {
            unset($contact->supporter_tags['und'][$tag_index]);
            $tids[$tag['tid']] = $tag['tid'];
          }
        }
      }
      if (!empty($tids)) {
        try {
          $contact->save();
        } catch (Exception $e) {
          $context['results']['failed_contacts'][$contact_id] = $e->getMessage();
        }
      }
      $context['sandbox']['progress']++;
    }
    $data = array(
      '@current' => $context['sandbox']['progress'],
      '@total'   => $context['sandbox']['max']
    );
    if ($this->tag) {
      $context['message'] = t('Added tags to @current out of @total supporters.', $data);
    }
    else {
      $context['message'] = t('Deleted tags from @current out of @total supporters.', $data);
    }
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  public function batchFinish($success, $results, $operations) {
    if (isset($results['failed_contacts'])) {
      if (count($results['failed_contacts']) < 11) {
        foreach($results['failed_contacts'] as $contact_id => $error_message) {
          if ($this->tag) {
            drupal_set_message(t("Couldn't add tags to contact with ID @contact_id: @message", array('@contact_id' => $contact_id, '@message' => $error_message)));
          }
          else {
            drupal_set_message(t("Couldn't remove tags from contact with ID @contact_id: @message", array('@contact_id' => $contact_id, '@message' => $error_message)));
          }
        }
      }
    }
  }
}
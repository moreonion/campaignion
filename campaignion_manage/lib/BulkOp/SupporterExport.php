<?php

namespace Drupal\campaignion_manage\BulkOp;

class SupporterExport implements BulkOpBatchInterface {
  public function title() { return t('Export Supporters'); }

  public function helpText() {
    return t('Export all currently selected supporters into a CSV file.');
  }

  public function formElement(&$element, &$form_state) {
    $options = array();
    foreach (field_info_instances('redhen_contact', 'contact') as $field_name => $field) {
      $options[$field_name] = $field['label'];
    }
    $element['export'] = array(
      '#type'     => 'select',
      '#title'    => t('Select one or more fields that you want to export.'),
      '#multiple' => TRUE,
      '#options'  => $options,
    );
  }

  public function apply($contact_ids, $values) {
    if (count($contact_ids) <= 0) {
      return;
    }
    $fields = array();
    foreach (field_info_instances('redhen_contact', 'contact') as $field_name => $field) {
      if (isset($values['export'][$field_name])) {
        $fields[$field_name] = $field['label'];
      }
    }
    $batch = array(
      'operations' => array(
        array('campaignion_manage_batch_process', array($this, $contact_ids, $fields)),
      ),
      'finished'         => 'campaignion_manage_batch_finished',
      'title'            => t('Export supporter data into CSV file ...'),
      'init_message'     => t('Start exporting supporter data...'),
      'progress_message' => t('Start exporting supporter data...'),
      'error_message'    => t('Encountered an error while exporting supporter data.'),
    );
    batch_set($batch);
  }

  public function batchApply($contact_ids, $fields, &$context) {
    $address_mapping = array(
      'street'  => 'thoroughfare',
      'country' => 'country',
      'zip'     => 'postal_code',
      'city'    => 'locality',
      'region'  => 'administrative_area',
    );
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress']   = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max']        = count($contact_ids);
      $context['sandbox']['csv_name']   = $context['results']['csv_name'] = tempnam(file_directory_temp(), 'CampaignionSupporterExport_' );
      $context['results']['bulkOp']     = $this;
      // create the CSV column header line
      $csv_header = array();
      foreach ($fields as $key => $value) {
        if ($key === 'field_address') {
          foreach ($address_mapping as $mapped_key => $key) {
            $csv_header[$mapped_key] = $mapped_key;
          }
        }
        else {
          $csv_header[$key] = $value;
        }
      }
      $handle = fopen($context['sandbox']['csv_name'], 'w');
      fputcsv($handle, $csv_header);
      fclose($handle);
    }
    $ids = array_slice(
      $contact_ids,
      $context['sandbox']['progress'],
      min($context['sandbox']['progress'] + 100, $context['sandbox']['max']),
      FALSE
    );
    if (($handle = fopen($context['sandbox']['csv_name'], 'a')) == FALSE) {
      $context['results']['errors'] = t('Couldn\'t open temporary file to export supporters.');
    }
    $contacts = redhen_contact_load_multiple($ids);
    foreach ($contacts as $contact) {
      $csv_line = array();
      $contact = new \Drupal\campaignion\Contact($contact);
      $exporter = new \Drupal\campaignion_manage\CampaignionContactExporter($contact, $address_mapping);
      foreach ($fields as $field_name => $field_label) {
        if (is_array($value = $exporter->value($field_name))) {
          $csv_line += $value;
        }
        else {
          $csv_line[$field_name] = $value;
        }
      }
      fputcsv($handle, $csv_line);
      $context['sandbox']['progress']++;
      $context['message'] = t('Exported @current out of @total supporters.', array('@current' => $context['sandbox']['progress'], '@total' => $context['sandbox']['max']));
    }
    fclose($handle);
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  public function batchFinish($success, $results, $operations) {
    if (isset($results['errors'])) {
      foreach($results['errors'] as $error_message) {
        drupal_set_message($error_message);
      }
    }
  }
}

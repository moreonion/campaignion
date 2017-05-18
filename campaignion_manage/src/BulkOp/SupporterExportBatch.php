<?php

namespace Drupal\campaignion_manage\BulkOp;

use \Drupal\campaignion\ContactTypeManager;

class SupporterExportBatch extends BatchBase {
  protected $exporter;
  protected $file = NULL;
  protected $fields;
  protected $filename;

  public function __construct(&$data) {
    $this->fields = $data['fields'];
    $this->filename = $data['csv_name'];
    $this->exporter = ContactTypeManager::instance()
      ->exporter('campaignion_manage');
  }

  public function start(&$context) {
    if (!($handle = fopen($this->filename, 'a'))) {
      $context['results']['errors'] = t('Couldn\'t open temporary file to export supporters.');
    }
    $this->file = $handle;
  }

  public function apply($contact, &$result) {
    $this->exporter->setContact($contact);
    $csv_line = array();
    foreach ($this->fields as $field_name => $field_label) {
      $value = $this->exporter->value($field_name);
      if (is_array($value)) {
        if (empty($value)) {
          $value = array($field_name => '');
        }
      }
      else {
        $value = array($field_name => $value);
      }
      $csv_line += $value;
    }
    fputcsv($this->file, $csv_line);
  }

  public function commit() {
    fclose($this->file);
  }
}

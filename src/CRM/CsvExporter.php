<?php

namespace Drupal\campaignion\CRM;

/**
 * Exporter specialised for producing rows for a table (ie. CSV file).
 */
class CsvExporter extends ExporterBase {

  /**
   * Get header row.
   *
   * @param int $row_num
   *   Row number.
   *
   * @return string[]
   *   The header row.
   */
  public function header($row_num = 0) {
    $row = [];
    foreach ($this->map as $k => $l) {
      $row[$k] = $l->header($row_num);
    }
    return $row;
  }

  /**
   * Generate the row for the current contact.
   *
   * @return string[]
   *   Stringified field values for the current contact.
   */
  public function row() {
    $row = [];
    foreach (array_keys($this->map) as $k) {
      $row[$k] = $this->value($k);
    }
    return $row;
  }

}

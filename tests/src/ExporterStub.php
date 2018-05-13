<?php

namespace Drupal\campaignion_csv\Tests;

use Drupal\campaignion_csv\Files\CsvFile;

/**
 * Exporter that yields a pre-defined set of rows.
 */
class ExporterStub {

  /**
   * Create new instance.
   *
   * @param string[][] $rows
   *   The rows that this exporter should yield.
   */
  public function __construct(array $rows = []) {
    $this->rows = $rows;
  }

  /**
   * Write the rows to a file.
   *
   * @param \Drupal\campaignion_csv\Files\CsvFile $file
   *   The target file.
   */
  public function writeTo(CsvFile $file) {
    foreach ($this->rows as $row) {
      if (is_array($row)) {
        $file->writeRow($row);
      }
      elseif ($row instanceof \Exception || $row instanceof \Throwable) {
        throw $row;
      }
    }
  }

}

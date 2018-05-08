<?php

namespace Drupal\campaignion_csv;

/**
 * Write CSV to a file.
 */
class CsvFile extends \SplFileObject {

  /**
   * Number of columns in the file.
   */
  protected $numberOfColumns = NULL;

  /**
   * Write an array of values to a file.
   *
   * @param string[] $row
   *   Array of strings to print to the CSV.
   *
   * @throws \InvalidArgumentException when the number of columns in the row
   *   doesn’t match the number of columns in the first row.
   */
  public function writeRow(array $row) {
    if (!isset($this->numberOfColumns)) {
      $this->numberOfColumns = count($row);
    }
    else {
      $column_count = count($row);
      if ($this->numberOfColumns != $column_count) {
        throw new \InvalidArgumentException("Expected {$this->numberOfColumns} columns, got $column_count instead.");
      }
    }
    $this->fputcsv($row);
  }

}

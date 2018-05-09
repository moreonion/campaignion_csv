<?php

namespace Drupal\campaignion_csv\Files;

/**
 * Write CSV to a file.
 */
class CsvFile extends \SplFileObject {

  /**
   * Number of columns in the file used to check later rows vs. the first row.
   *
   * @var int
   */
  protected $numberOfColumns = NULL;

  /**
   * Write an array of values to a file.
   *
   * @param string[] $row
   *   Array of strings to print to the CSV.
   *
   * @throws \InvalidArgumentException
   *   When the number of cells in the row doesn’t match the number of cells in
   *   the first row an \InvalidArgumentException is thrown.
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

<?php

namespace Drupal\campaignion_csv;

/**
 * Write CSV to a file.
 */
class CsvWriter {

  /**
   * CSV output is written to this file.
   *
   * @var \SplFileObject
   */
  protected $output;

  /**
   * Number of columns in the file.
   */
  protected $numberOfColumns = NULL;

  /**
   * Create a new Csv writer instance by passing a file object.
   */
  public function __construct(\SplFileObject $file_output) {
    $this->output = $file_output;
    static::setCsvControl();
  }

  /**
   * Set CSV format options.
   *
   * @see \SplFileObject::setCsvControl()
   */
  public function setCsvControl($delimiter = ',', $enclosure = '"', $escape = '\\') {
    $this->output->setCsvControl($delimiter, $enclosure, $escape);
  }

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
    $this->output->fputcsv($row);
  }

}

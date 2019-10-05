<?php

namespace Drupal\campaignion_csv\Files;

/**
 * Interface used for all Csv-file types.
 */
interface CsvFileInterface {

  /**
   * Write a CSV row into the file.
   *
   * @param string[] $row
   *   An array of values to write.
   */
  public function writeRow(array $row);

}

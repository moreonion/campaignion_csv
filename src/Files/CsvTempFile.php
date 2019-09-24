<?php

namespace Drupal\campaignion_csv\Files;

/**
 * A temporary CSV file.
 */
class CsvTempFile extends \SplTempFileObject implements CsvFileInterface {

  use CsvMixin;

  /**
   * Copy the contents of this temporary file into another file.
   *
   * @param \SplFileObject $file
   *   The target file.
   * @param int $chunk_size
   *   The number of bytes copied in each iteration.
   */
  public function dumpInto(\SplFileObject $file, $chunk_size = 8192) {
    $this->rewind();
    while (!$this->eof()) {
      $file->fwrite($this->fread($chunk_size));
    }
  }

}

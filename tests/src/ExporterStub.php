<?php

namespace Drupal\campaignion_csv\Tests;

use Drupal\campaignion_csv\CsvFile;

class ExporterStub {

  public function __construct(array $rows = []) {
    $this->rows = $rows;
  }

  public function writeTo(CsvFile $file) {
    foreach ($this->rows as $row) {
      $file->writeRow($row);
    }
  }

}

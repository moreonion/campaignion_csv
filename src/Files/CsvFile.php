<?php

namespace Drupal\campaignion_csv\Files;

/**
 * A persistent CSV file.
 */
class CsvFile extends \SplFileObject implements CsvFileInterface {

  use CsvMixin;

}

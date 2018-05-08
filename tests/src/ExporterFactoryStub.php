<?php

namespace Drupal\campaignion_csv\Tests;

/**
 * Factory that always returns the same object.
 */
class ExporterFactoryStub {

  public static function withRows(array $rows = []) {
    return new static(new ExporterStub($rows));
  }

  public function __construct($exporter) {
    $this->exporter = $exporter;
  }

  public function createExporter() {
    return $this->exporter;
  }

}

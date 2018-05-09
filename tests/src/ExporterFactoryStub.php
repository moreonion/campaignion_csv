<?php

namespace Drupal\campaignion_csv\Tests;

/**
 * Factory that always returns the same object.
 */
class ExporterFactoryStub {

  /**
   * Create a factory that returns an exporter yielding pre-defined rows.
   *
   * @param string[][] $rows
   *   The CSV rows that the exporter should yield.
   */
  public static function withRows(array $rows = []) {
    return new static(new ExporterStub($rows));
  }

  /**
   * Create a new instance.
   *
   * @param mixed $exporter
   *   The exporter.
   */
  public function __construct($exporter) {
    $this->exporter = $exporter;
  }

  /**
   * Get the exporter.
   *
   * @return mixed
   *   The exporter.
   */
  public function createExporter() {
    return $this->exporter;
  }

}

<?php

namespace Drupal\campaignion_csv\Exporter;

/**
 * Simple info based exporter factory.
 */
class ExporterFactory {

  /**
   * Create new factory based on the info array.
   *
   * @param array $info
   *   Info array as passed in hook_campaignion_csv_info().
   */
  public static function fromInfo(array $info) {
    return new static($info);
  }

  /**
   * Create new instance.
   *
   * @param array $info
   *   Info array as passed in hook_campaignion_csv_info().
   */
  public function __construct(array $info) {
    $this->info = $info;
  }

  /**
   * Instantiate the exporter.
   */
  public function createExporter() {
    $class = $this->info['class'];
    return $class::fromInfo($this->info);
  }

}

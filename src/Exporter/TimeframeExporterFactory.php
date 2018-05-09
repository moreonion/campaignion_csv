<?php

namespace Drupal\campaignion_csv\Exporter;

use Drupal\campaignion_csv\Timeframe;

/**
 * Instantiate exporters based on a timeframe.
 */
class TimeframeExporterFactory extends ExporterFactory {

  protected $info;

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
   *
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   Export data for this timeframe.
   */
  public function createExporter(Timeframe $timeframe) {
    $class = $this->info['class'];
    return $class::fromInfo($timeframe, $this->info);
  }

}

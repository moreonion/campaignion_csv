<?php

namespace Drupal\campaignion_csv\Exporter;

use Drupal\campaignion_csv\Timeframe;

/**
 * Instantiate exporters based on a timeframe.
 */
class TimeframeExporterFactory {

  public static function fromInfo($info) {
    return new static($info);
  }

  public function __construct($info) {
    $this->info = $info;
  }

  public function createExporter(Timeframe $timeframe) {
    $class = $this->info['class'];
    return $class::fromInfo($timeframe, $this->info);
  }

}

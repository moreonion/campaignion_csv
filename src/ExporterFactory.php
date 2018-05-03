<?php

namespace Drupal\campaignion_csv;

class ExporterFactory {

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


<?php

namespace Drupal\campaignion_csv\Exporter;

/**
 * Simple info based exporter factory.
 */
class ExporterFactory {

  public static function fromInfo($info) {
    return new static($info);
  }

  public function __construct($info) {
    $this->info = $info;
  }

  public function createExporter() {
    $class = $this->info['class'];
    return $class::fromInfo($this->info);
  }

}


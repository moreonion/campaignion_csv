<?php

namespace Drupal\campaignion_csv;

/**
 * Represents one (to be) exported file in the managed directory.
 */
class File {

  public function __construct($path, Timeframe $timeframe) {
    $this->path = $path;
    $this->timeframe = $timeframe;
  }

  public function setExporterFactory($factory) {
    $this->exporterFactory = $factory;
  }

  protected function ensureDir() {
    $dir_path = dirname($this->path);
    if (!is_dir($dir_path)) {
      if (!drupal_mkdir($dir_path, NULL, TRUE)) {
        throw new \RuntimeException("Unable to create directory: $dir_path");
      }
    }
  }

  protected function openFile() {
    return new CsvFile($this->path, 'w');
  }

  protected function needsBuild() {
    list($start, $end) = $this->timeframe->getTimestamps();
    return !file_exists($this->path) || filemtime($this->path) < $end;
  }

  public function generate() {
    if ($this->needsBuild()) {
      $this->ensureDir();
      $exporter = $this->exporterFactory->createExporter($this->timeframe);
      $exporter->writeTo($this->openFile());
    }
  }

}

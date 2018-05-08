<?php

namespace Drupal\campaignion_csv;

/**
 * Timeframe based export file in the directory.
 */
class TimeframeFileInfo extends \SplFileInfo implements ExportableFileInfoInterface {

  protected $timeframe;
  protected $exporterFactory;
  protected $refreshInterval;

  /**
   * Create a new instance based on a timeframe.
   *
   * @param string $path
   *   Full path to the file (even if it does not yet exist).
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   The timeframe for which data should be exported.
   * @param \DateInterval $refresh_interval
   *   The minimum interval that needs to pass between two builds of the file.
   */
  public function __construct($path, Timeframe $timeframe, \DateInterval $refresh_interval) {
    parent::__construct($path);
    $this->timeframe = $timeframe;
    $this->refreshInterval = $refresh_interval;
  }

  /**
   * {@inheritdoc}
   */
  public function setExporterFactory($factory) {
    $this->exporterFactory = $factory;
  }

  /**
   * Create the containing directory if needed.
   */
  protected function ensureDir() {
    $dir_path = $this->getPath();
    if (!is_dir($dir_path)) {
      if (!drupal_mkdir($dir_path, NULL, TRUE)) {
        throw new \RuntimeException("Unable to create directory: $dir_path");
      }
    }
  }

  /**
   * Open the file as a CSV-file.
   *
   * @return \Drupal\campaignion_csv\CsvFile
   *   The opened CSV-file.
   */
  public function openFile($open_mode = 'r', $use_include_path = FALSE, $context = NULL) {
    return new CsvFile($this->getPathName(), $open_mode, $use_include_path, $context);
  }

  /**
   * Checks whether the file needs to be rebuilt.
   *
   * @return bool
   *   TRUE if the file should be rebuilt otherwise FALSE.
   */
  protected function needsBuild() {
    list($start, $end) = $this->timeframe->getTimestamps();
    if (!$this->isFile()) {
      return TRUE;
    }
    $mtime = $this->getMTime();
    if ($mtime < $end) {
      // The interval was still ongoing the last time the file was generated.
      // We regenerate the file if the refresh interval has passed since then.
      return $this->refreshInterval->format('s') < time() - $mtime;
    }
    return FALSE;
  }

  /**
   * Create or rebuild the file if needed.
   */
  public function update() {
    if ($this->needsBuild()) {
      $this->ensureDir();
      $exporter = $this->exporterFactory->createExporter($this->timeframe);
      $exporter->writeTo($this->openFile('w'));
    }
  }

}

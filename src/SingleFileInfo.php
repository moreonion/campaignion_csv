<?php

namespace Drupal\campaignion_csv;

/**
 * A single file that’s updated regularly.
 */
class SingleFileInfo extends \SplFileInfo implements ExportableFileInfoInterface {

  protected $exporterFactory;
  protected $refreshInterval;

  /**
   * Create a new instance based on a timeframe.
   *
   * @param string $path
   *   Full path to the file (even if it does not yet exist).
   * @param \DateInterval $refresh_interval
   *   The minimum interval that needs to pass between two builds of the file.
   */
  public function __construct($path, \DateInterval $refresh_interval) {
    parent::__construct($path);
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
    if (!$this->isFile()) {
      return TRUE;
    }
    // Regenerate the file if the refresh interval has passed since the last build.
    return $this->refreshInterval->format('s') < time() - $this->getMTime();
  }

  /**
   * Create or rebuild the file if needed.
   */
  public function update() {
    if ($this->needsBuild()) {
      $this->ensureDir();
      $exporter = $this->exporterFactory->createExporter();
      $exporter->writeTo($this->openFile('w'));
    }
  }

}

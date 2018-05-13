<?php

namespace Drupal\campaignion_csv\Files;

/**
 * A single file that’s updated regularly.
 */
class CsvFileInfo extends \SplFileInfo implements ExportableInterface {

  protected $exporterFactory;

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
   * Create temporary file for exporting.
   */
  protected function temporaryFile($open_mode = 'r', $use_include_path = FALSE, $context = NULL) {
    $path = tempnam(sys_get_temp_dir(), 'csv-export');
    return new CsvFile($path, $open_mode, $use_include_path, $context);
  }

  /**
   * Move temporary file to the destination.
   */
  protected function moveToDestination(CsvFile $tmp_file) {
    $path = $tmp_file->getPathName();
    copy($path, $this->getPathName());
    unlink($path);
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
  }

  /**
   * Create or rebuild the file if needed.
   */
  public function update() {
    if ($this->needsBuild()) {
      $this->ensureDir();
      $tmp_file = $this->temporaryFile('w');
      $this->createExporter()->writeTo($tmp_file);
      $this->moveToDestination($tmp_file);
    }
  }

  /**
   * Create the exporter.
   */
  protected function createExporter() {
    return $this->exporterFactory->createExporter();
  }

}

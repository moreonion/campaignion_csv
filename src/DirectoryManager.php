<?php

namespace Drupal\campaignion_csv;

/**
 * Manages exports in a directory.
 *
 * - Decides which export files are created.
 * - Deletes obsolete exports.
 */
class DirectoryManager {

  protected $path;
  protected $files;

  /**
   * Create a new directory manager from global configuration.
   */
  public static function fromConfig() {
    $path = variable_get_value('campaignion_csv_path');
    $exports = module_invoke_all('campaignion_csv_info');
    drupal_alter('campaignion_csv_info', $exports);
    return new static($path, $exports);
  }

  /**
   * Create a new directory manager.
   *
   * @param string $path
   *   The path to the managed directory.
   * @param array $info
   *   Result of calling campaignion_csv_info hooks.
   */
  public function __construct($path, array $info) {
    $this->path = $path;
    $this->info = $info;
    $this->generateFiles();
  }

  /**
   * Generate all files that need updates.
   */
  public function build() {
    foreach ($this->files as $file) {
      $file->update();
    }
  }

  /**
   * Remove any files (and sub-directories) that are not managed by this module.
   */
  public function cleanup() {
    //$it = new \RecursiveDirectoryIterator($this->path, \FileSystemIterator::SKIP_DOTS);
  }

  /**
   * Read the info from the info and instantiate the plugins.
   */
  protected function generateFiles() {
    $this->files = [];
    foreach ($this->info as $info) {
      $class = $info['file_pattern']['class'];
      $file_pattern = $class::fromInfo($info['file_pattern']);
      $class = $info['exporter']['factory_class'];
      $factory = $class::fromInfo($info['exporter']);
      foreach ($file_pattern->expand($this->path) as $path => $file) {
        $file->setExporterFactory($factory);
        $this->files[$path] = $file;
      }
    }
  }

}

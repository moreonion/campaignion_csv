<?php

namespace Drupal\campaignion_csv;

use Drupal\campaignion_csv\Exporter\ExporterFactory;

/**
 * Manages exports in a directory.
 *
 * - Decides which export files are created.
 * - Deletes obsolete exports.
 */
class DirectoryManager {

  protected $path;
  protected $files;
  protected $softTimeLimit;
  protected $softMemoryLimit;

  /**
   * @var int
   * Unix timestamp for when the build has started.
   */
  protected $buildStartedTime;

  /**
   * @var int
   * Memory usage at the beginning of the build, in bytes.
   */
  protected $buildStartedMemory;

  /**
   * Create a new directory manager from global configuration.
   */
  public static function fromConfig() {
    $path = variable_get_value('campaignion_csv_path');
    $exports = module_invoke_all('campaignion_csv_info');
    drupal_alter('campaignion_csv_info', $exports);
    $time_limit = variable_get_value('campaignion_csv_time_limit');
    $memory_limit = variable_get_value('campaignion_csv_memory_limit');
    return new static($path, $exports, $time_limit, $memory_limit);
  }

  /**
   * Create a new directory manager.
   *
   * @param string $path
   *   The path to the managed directory.
   * @param array $info
   *   Result of calling campaignion_csv_info hooks.
   * @param int $time_limit
   *   Soft time limit for building files in seconds.
   * @param int $memory_limit
   *   Limit to leaked memory during the export in bytes.
   */
  public function __construct($path, array $info, $time_limit, $memory_limit) {
    $this->path = $path;
    $this->info = $info;
    $this->softTimeLimit = $time_limit;
    $this->softMemoryLimit = $memory_limit;
    $this->generateFiles();
  }

  /**
   * Generate all files that need updates.
   */
  public function build() {
    $files = $this->files;
    while ($files && !$this->softLimitsExceeded()) {
      $file = array_shift($files);
      $file->update();
    }
  }

  /**
   * Read the info from the info and instantiate the plugins.
   */
  protected function generateFiles() {
    $this->files = [];
    foreach ($this->info as $info) {
      $class = $info['file_pattern']['class'];
      $file_pattern = $class::fromInfo($info['file_pattern']);
      $factory = ExporterFactory::fromInfo($info['exporter']);
      foreach ($file_pattern->expand($this->path) as $path => $file) {
        $file->setExporterFactory($factory);
        $this->files[$path] = $file;
      }
    }
  }

  /**
   * Check whether memory or time limits has been exceeded.
   *
   * @return bool
   *   TRUE if limits has been exceeded, otherwise FALSE.
   */
  protected function softLimitsExceeded() {
    gc_collect_cycles();
    if (!isset($this->buildStartedTime)) {
      $this->buildStartedTime = time();
      $this->buildStartedMemory = memory_get_usage();
    }
    $time_limit_exceeded = time() - $this->buildStartedTime > $this->softTimeLimit;
    $memory_limit_exceeded = memory_get_usage() - $this->buildStartedMemory > $this->softMemoryLimit;
    return $time_limit_exceeded || $memory_limit_exceeded;
  }

}

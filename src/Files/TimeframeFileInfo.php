<?php

namespace Drupal\campaignion_csv\Files;

use Drupal\campaignion_csv\Timeframe;

/**
 * Timeframe based export file in the directory.
 */
class TimeframeFileInfo extends SingleFileInfo {

  protected $timeframe;

  /**
   * Create a new instance.
   *
   * @param string $path
   *   Full path to the file (even if it does not yet exist).
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   The timeframe for which data should be exported.
   * @param \DateInterval $refresh_interval
   *   The minimum interval that needs to pass between two builds of the file.
   */
  public function __construct($path, Timeframe $timeframe, \DateInterval $refresh_interval) {
    parent::__construct($path, $refresh_interval);
    $this->timeframe = $timeframe;
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
   * Create the exporter.
   */
  protected function createExporter() {
    $info['timeframe'] = $this->timeframe;
    return $this->exporterFactory->createExporter($info);
  }

}

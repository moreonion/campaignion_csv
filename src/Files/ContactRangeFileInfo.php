<?php

namespace Drupal\campaignion_csv\Files;

/**
 * Range based export file in the directory.
 */
class ContactRangeFileInfo extends SingleFileInfo {

  protected $bundle;
  protected $range;

  /**
   * Create a new instance.
   *
   * @param string $path
   *   Full path to the file (even if it does not yet exist).
   * @param int[] $range
   *   Start and end of the range.
   * @param \DateInterval $refresh_interval
   *   The minimum interval that needs to pass between two builds of the file.
   */
  public function __construct($path, array $range, \DateInterval $refresh_interval) {
    parent::__construct($path, $refresh_interval);
    $this->range = $range;
  }

  /**
   * Create the exporter.
   */
  protected function createExporter() {
    $info['range'] = $this->range;
    return $this->exporterFactory->createExporter($info);
  }

}

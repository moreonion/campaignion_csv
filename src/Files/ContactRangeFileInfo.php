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
   * @param string $bundle
   *   The redhen contact type that’s being exported.
   * @param int[] $range
   *   Start and end of the range.
   * @param \DateInterval $refresh_interval
   *   The minimum interval that needs to pass between two builds of the file.
   */
  public function __construct($path, $bundle, array $range, \DateInterval $refresh_interval) {
    parent::__construct($path, $refresh_interval);
    $this->bundle = $bundle;
    $this->range = $range;
  }

  /**
   * Checks whether the file needs to be rebuilt.
   *
   * @return bool
   *   TRUE if the file should be rebuilt otherwise FALSE.
   */
  protected function needsBuild() {
    if (parent::needsBuild()) {
      if (!$this->isFile()) {
        return TRUE;
      }

      // Conservative estimate for the last start time. Exports don’t take 2h.
      $mtime = $this->getMTime();
      $last_start_time_estimate = $mtime - 7200;
      // Check whether at least one contact changed since then.
      list($min_id, $max_id) = $this->range;
      return (bool) db_select('redhen_contact', 'c')
        ->fields('c', ['contact_id'])
        ->condition('type', $this->bundle)
        ->condition('contact_id', $min_id, '>=')
        ->condition('contact_id', $max_id, '<')
        ->condition('updated', $last_start_time_estimate, '>=')
        ->range(0, 1)
        ->execute()
        ->fetch();
    }
    return FALSE;
  }

  /**
   * Create the exporter.
   */
  protected function createExporter() {
    $info['range'] = $this->range;
    return $this->exporterFactory->createExporter($info);
  }

}

<?php

namespace Drupal\campaignion_csv\Files;

/**
 * File pattern that creates monthly files.
 */
class MonthlyFilePattern extends DateIntervalFilePattern {

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info-array as defined in hook_campaignion_csv_info(). Keys are:
   *   - path: The path pattern for the file relative to the root. The path
   *     is expanded using `strftime()`.
   *   - retention_period: A \DateInterval specifying how long old files should
   *     be kept (or generated).
   *   - include_current: Whether the ongoing month should be included.
   *   - refresh_interval: A \DateInterval that defines how often files for the
   *     ongoing period are regenerated.
   * @param \DateTimeInterface $now
   *   The time considered to be now. Defaults to the date and time.
   */
  public static function fromInfo(array $info, \DateTimeInterface $now = NULL) {
    $info['interval'] = new \DateInterval('P1M');
    $info['anchor_format'] = 'Y-m-01';
    return parent::fromInfo($info, $now);
  }

}

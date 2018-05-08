<?php

namespace Drupal\campaignion_csv\FilePattern;

use Drupal\campaignion_csv\FilePatternInterface;
use Drupal\campaignion_csv\TimeframeFileInfo;
use Drupal\campaignion_csv\Timeframe;

/**
 * File pattern that creates monthly files.
 */
class Monthly implements FilePatternInterface {

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
   */
  public static function fromInfo(array $info, \DateTimeInterface $now = NULL) {
    if (!$now) {
      $now = new \DateTime();
    }
    $info += [
      'include_current' => TRUE,
      'refresh_interval' => new \DateInterval('PT23H30M'),
    ];
    $one_month = new \DateInterval('P1M');

    $end = new \DateTimeImmutable($now->format('Y-m') . '-01');
    $start = $end->sub($info['retention_period']);
    if ($info['include_current']) {
      $end = $end->add($one_month);
    }
    $period = new \DatePeriod($start, $one_month, $end);
    return new static($info['path'], $period, $info['refresh_interval']);
  }

  /**
   * Create a new monthly file pattern.
   *
   * @param string $path
   *   The path pattern in `strftime()`-format.
   * @param \DatePeriod $period
   *   A period capable for generating the start time for each month.
   * @param \DateInterval $refresh_interval
   *   The minimum interval that needs to pass between two builds of the file.
   */
  public function __construct($path, \DatePeriod $period, \DateInterval $refresh_interval) {
    $this->pathPattern = $path;
    $this->period = $period;
    $this->refreshInterval = $refresh_interval;
  }

  /**
   * Expand the file pattern and create the specfic files.
   *
   * @param string $root
   *   The path to the root-directory. The pattern is interpreted relative to
   *   the root-directory.
   *
   * @return \Drupal\campaignion_csv\ExportableFileInfoInterface[]
   *   Array of file info objects keyed by their expanded path.
   */
  public function expand($root) {
    $files = [];
    $interval = new \DateInterval('P1M');
    foreach ($this->period as $start) {
      $path = strftime($this->pathPattern, $start->getTimestamp());
      $file = new TimeframeFileInfo($root . '/' . $path, new Timeframe($start, $interval), $this->refreshInterval);
      $files[$path] = $file;
    }
    return $files;
  }

}

<?php

namespace Drupal\campaignion_csv\Files;

use Drupal\campaignion_csv\Timeframe;

/**
 * File pattern that creates files based on a date interval.
 */
class DateIntervalFilePattern implements FilePatternInterface {

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info-array as defined in hook_campaignion_csv_info(). Keys are:
   *   - path: The path pattern for the file relative to the root. The path
   *     is expanded using `strftime()`.
   *   - interval: A \DateInterval specifying the length of the time interval
   *     covered by a single file.
   *   - anchor_format: ISO date format string used to get the start of the
   *     current interval based and the current \DateTime object.
   *   - retention_period: A \DateInterval specifying how long old files should
   *     be kept (or generated).
   *   - include_current: Whether the ongoing month should be included.
   *   - refresh_interval: A \DateInterval that defines how often files for the
   *     ongoing period are regenerated.
   * @param \DateTimeInterface $now
   *   The time considered to be now. Defaults to the date and time.
   */
  public static function fromInfo(array $info, \DateTimeInterface $now = NULL) {
    if (!$now) {
      $now = new \DateTime();
    }
    $info += [
      'include_current' => TRUE,
    ];
    $interval = $info['interval'];

    $end = new \DateTimeImmutable($now->format($info['anchor_format']));
    $start = $end->sub($info['retention_period']);
    if ($info['include_current']) {
      $end = $end->add($interval);
    }
    $period = new \DatePeriod($start, $interval, $end);
    return new static($info['path'], $period, $info);
  }

  /**
   * Create a new monthly file pattern.
   *
   * @param string $path
   *   The path pattern in `strftime()`-format.
   * @param \DatePeriod $period
   *   A period capable for generating the start time for each month.
   * @param array $info
   *   Info-array that’s simply passed on to the FileInfo objects.
   */
  public function __construct($path, \DatePeriod $period, array $info) {
    $this->pathPattern = $path;
    $this->period = $period;
    $this->info = $info;
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
    $interval = $this->info['interval'];
    foreach ($this->period as $start) {
      $path = strftime($this->pathPattern, $start->getTimestamp());
      $info = [
        'path' => $root . '/' . $path,
        'timeframe' => new Timeframe($start, $interval),
      ] + $this->info;
      $files[$path] = TimeframeFileInfo::fromInfo($info);
    }
    return $files;
  }

}

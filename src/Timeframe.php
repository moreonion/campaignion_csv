<?php

namespace Drupal\campaignion_csv;

/**
 * Utility class that represents a timeframe.
 */
class Timeframe {

  /**
   * Create timeframe by passing the start and interval.
   *
   * @param \DateTimeImmutable $start
   *   The start of the timeframe.
   * @param \DateInterval $interval
   *   The duration of the timeframe.
   */
  public function __construct(\DateTimeImmutable $start, \DateInterval $interval) {
    $this->start = $start;
    $this->interval = $interval;
  }

  /**
   * Get the start and end timestamp for this timeframe.
   *
   * @return int[]
   *   Two timestamps.
   */
  public function getTimestamps() {
    return [
      $this->start->getTimeStamp(),
      $this->start->add($this->interval)->getTimestamp(),
    ];
  }

}

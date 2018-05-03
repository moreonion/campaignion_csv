<?php

namespace Drupal\campaignion_csv;

/**
 * Utility class that represents a timeframe.
 */
class Timeframe {

  public function __construct(\DateTime $start, \DateInterval $interval) {
    $this->start = $start;
    $this->interval = $interval;
  }

  public function getTimestamps() {
    return [
      $this->start->getTimeStamp(),
      (clone $this->start)->add($this->interval)->getTimestamp(),
    ];
  }

}

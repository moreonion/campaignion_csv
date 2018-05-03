<?php

namespace Drupal\campaignion_csv\FilePattern;

use Drupal\campaignion_csv\File;
use Drupal\campaignion_csv\Timeframe;

/**
 *
 */
class Monthly {

  public static function fromInfo(array $info, \DateTime $now = NULL) {
    if (!$now) {
      $now = new \DateTime();
    }
    $current = new \DateTime($now->format('Y-m') . '-01');
    $start = (clone $current)->sub($info['retention_period']);
    $period = new \DatePeriod($start, new \DateInterval('P1M'), $current);
    return new static($info['path'], $period);
  }

  public function __construct($path, \DatePeriod $period) {
    $this->pathPattern = $path;
    $this->period = $period;
  }

  public function expand($root) {
    $interval = new \DateInterval('P1M');
    foreach ($this->period as $start) {
      $path = strftime($this->pathPattern, $start->getTimestamp());
      $file = new File($root . '/' . $path, new Timeframe($start, $interval));
      yield $path => $file;
    }
  }

}

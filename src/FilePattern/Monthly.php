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
    $info += [
      'include_current' => TRUE,
    ];
    $one_month = new \DateInterval('P1M');

    $end = new \DateTimeImmutable($now->format('Y-m') . '-01');
    $start = $end->sub($info['retention_period']);
    if ($info['include_current']) {
      $end = $end->add($one_month);
    }
    $period = new \DatePeriod($start, $one_month, $end);
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

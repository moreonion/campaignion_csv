<?php

namespace Drupal\campaignion_csv\Files;

/**
 * Test a daily file pattern.
 */
class DailyFilePatternTest extends \DrupalUnitTestCase {

  /**
   * Test creating files based on the info array.
   */
  public function testFromInfo() {
    $info = [
      'path' => 'a/%Y-%m-%d',
      'interval' => new \DateInterval('P1D'),
      'retention_period' => new \DateInterval('P1W'),
      'anchor_format' => 'Y-m-d',
      'include_current' => FALSE,
    ];
    $now = new \DateTime('2018-04-15');
    $file_pattern = DateIntervalFilePattern::fromInfo($info, $now);
    $this->assertEqual([
      'a/2018-04-08',
      'a/2018-04-09',
      'a/2018-04-10',
      'a/2018-04-11',
      'a/2018-04-12',
      'a/2018-04-13',
      'a/2018-04-14',
    ], array_keys($file_pattern->expand('/root')));
  }

}

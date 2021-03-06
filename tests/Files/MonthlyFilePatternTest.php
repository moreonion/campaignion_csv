<?php

namespace Drupal\campaignion_csv\Files;

/**
 * Test the monthly file pattern.
 */
class MonthlyFilePatternTest extends \DrupalUnitTestCase {

  /**
   * Test that array keys match the path pattern.
   */
  public function testExpandKeysArePaths() {
    $pattern = 'a/%Y-%m';
    $interval = new \DateInterval('P1M');
    $period = new \DatePeriod(new \DateTimeImmutable('2018-01-01'), $interval, new \DateTimeImmutable('2018-04-01'));
    $file_pattern = new MonthlyFilePattern($pattern, $period, $interval, []);
    $this->assertEqual([
      'a/2018-01',
      'a/2018-02',
      'a/2018-03',
    ], array_keys($file_pattern->expand('/root')));

  }

  /**
   * Test that expand returns proper FileInfo instances.
   */
  public function testExpandReturnsFiles() {
    $pattern = 'a/%Y-%m';
    $interval = new \DateInterval('P1M');
    $period = new \DatePeriod(new \DateTimeImmutable('2018-01-01'), $interval, new \DateTimeImmutable('2018-04-01'));
    $file_pattern = new MonthlyFilePattern($pattern, $period, $interval, []);
    $files = $file_pattern->expand('/root');
    $this->assertCount(3, $files);
    $this->assertContainsOnlyInstancesOf(TimeframeFileInfo::class, $files);
  }

  /**
   * Test creating files based on the info array.
   */
  public function testFromInfo() {
    $info = [
      'path' => 'a/%Y-%m',
      'retention_period' => new \DateInterval('P3M'),
      'include_current' => FALSE,
    ];
    $now = new \DateTime('2018-04-15');
    $file_pattern = MonthlyFilePattern::fromInfo($info, $now);
    $this->assertEqual([
      'a/2018-01',
      'a/2018-02',
      'a/2018-03',
    ], array_keys($file_pattern->expand('/root')));
  }

  /**
   * Test that current month is included if the option is set.
   */
  public function testFromInfoIncludingCurrentMonth() {
    $info = [
      'path' => 'a/%Y-%m',
      'retention_period' => new \DateInterval('P3M'),
      'include_current' => TRUE,
    ];
    $now = new \DateTime('2018-04-15');
    $file_pattern = MonthlyFilePattern::fromInfo($info, $now);
    $this->assertEqual([
      'a/2018-01',
      'a/2018-02',
      'a/2018-03',
      'a/2018-04',
    ], array_keys($file_pattern->expand('/root')));
  }

}

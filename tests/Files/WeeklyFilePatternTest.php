<?php

namespace Drupal\campaignion_csv\Files;

/**
 * Test the weekly file pattern.
 */
class WeeklyFilePatternTest extends \DrupalUnitTestCase {

  /**
   * Test that array keys match the path pattern.
   */
  public function testExpandKeysArePaths() {
    $pattern = 'a/%Y-%m-%d';
    $interval = new \DateInterval('P1W');
    $period = new \DatePeriod(new \DateTimeImmutable('2020-07-27'), $interval, new \DateTimeImmutable('2020-08-11'));
    $file_pattern = new WeeklyFilePattern($pattern, $period, $interval, []);
    $this->assertEqual([
      'a/2020-07-27',
      'a/2020-08-03',
      'a/2020-08-10',
    ], array_keys($file_pattern->expand('/root')));

  }

  /**
   * Test that expand returns proper FileInfo instances.
   */
  public function testExpandReturnsFiles() {
    $pattern = 'a/%Y-%m-%d';
    $interval = new \DateInterval('P1W');
    $period = new \DatePeriod(new \DateTimeImmutable('2020-07-27'), $interval, new \DateTimeImmutable('2020-08-11'));
    $file_pattern = new WeeklyFilePattern($pattern, $period, $interval, []);
    $files = $file_pattern->expand('/root');
    $this->assertCount(3, $files);
    $this->assertContainsOnlyInstancesOf(TimeframeFileInfo::class, $files);
  }

  /**
   * Test creating files based on the info array.
   */
  public function testFromInfo() {
    $info = [
      'path' => 'a/%Y-%m-%d',
      'retention_period' => new \DateInterval('P3W'),
      'include_current' => FALSE,
    ];
    $now = new \DateTimeImmutable('2020-08-20');
    $file_pattern = WeeklyFilePattern::fromInfo($info, $now);
    $this->assertEqual([
      'a/2020-08-03',
      'a/2020-08-10',
    ], array_keys($file_pattern->expand('/root')));
  }

  /**
   * Test that current month is included if the option is set.
   */
  public function testFromInfoIncludingCurrentMonth() {
    $info = [
      'path' => 'a/%Y-%m-%d',
      'retention_period' => new \DateInterval('P3W'),
      'include_current' => TRUE,
    ];
    $now = new \DateTimeImmutable('2020-08-20');
    $file_pattern = WeeklyFilePattern::fromInfo($info, $now);
    $this->assertEqual([
      'a/2020-08-03',
      'a/2020-08-10',
      'a/2020-08-17',
    ], array_keys($file_pattern->expand('/root')));
  }

  /**
   * Test a retention period that can’t be expressed in weeks.
   */
  public function testFromInfoNonWeeklyRetention() {
    $info = [
      'path' => 'a/%Y-%m-%d',
      'retention_period' => new \DateInterval('P2M'),
      'include_current' => TRUE,
    ];
    $now = new \DateTimeImmutable('2020-08-20');
    $file_pattern = WeeklyFilePattern::fromInfo($info, $now);
    $this->assertEqual([
      'a/2020-06-22',
      'a/2020-06-29',
      'a/2020-07-06',
      'a/2020-07-13',
      'a/2020-07-20',
      'a/2020-07-27',
      'a/2020-08-03',
      'a/2020-08-10',
      'a/2020-08-17',
    ], array_keys($file_pattern->expand('/root')));
  }

  /**
   * Test with now on monday.
   */
  public function testOnMonday() {
    $info = [
      'path' => 'a/%Y-%m-%d',
      'retention_period' => new \DateInterval('P2M'),
      'include_current' => FALSE,
    ];
    $now = new \DateTimeImmutable('2020-08-17');
    $file_pattern = WeeklyFilePattern::fromInfo($info, $now);
    $this->assertEqual([
      'a/2020-06-22',
      'a/2020-06-29',
      'a/2020-07-06',
      'a/2020-07-13',
      'a/2020-07-20',
      'a/2020-07-27',
      'a/2020-08-03',
      'a/2020-08-10',
    ], array_keys($file_pattern->expand('/root')));
  }

}

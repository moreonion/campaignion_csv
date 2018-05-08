<?php

namespace Drupal\campaignion_csv\FilePattern;

use Drupal\campaignion_csv\TimeframeFileInfo;

class MonthlyFilePatternTest extends \DrupalUnitTestCase {

  public function testExpandKeysArePaths() {
    $pattern = 'a/%Y-%m';
    $period = new \DatePeriod(new \DateTimeImmutable('2018-01-01'), new \DateInterval('P1M'), new \DateTimeImmutable('2018-04-01'));
    $refresh_interval = new \DateInterval('PT24H');
    $file_pattern = new Monthly($pattern, $period, $refresh_interval);
    $this->assertEqual([
      'a/2018-01',
      'a/2018-02',
      'a/2018-03',
    ], array_keys($file_pattern->expand('/root')));

  }

  public function testExpandReturnsFiles() {
    $pattern = 'a/%Y-%m';
    $period = new \DatePeriod(new \DateTimeImmutable('2018-01-01'), new \DateInterval('P1M'), new \DateTimeImmutable('2018-04-01'));
    $refresh_interval = new \DateInterval('PT24H');
    $file_pattern = new Monthly($pattern, $period, $refresh_interval);
    $files = $file_pattern->expand('/root');
    $this->assertCount(3, $files);
    $this->assertContainsOnlyInstancesOf(TimeframeFileInfo::class, $files);
  }

  public function testFromInfo() {
    $info = [
      'path' => 'a/%Y-%m',
      'retention_period' => new \DateInterval('P3M'),
      'include_current' => FALSE,
    ];
    $now = new \DateTime('2018-04-15');
    $file_pattern = Monthly::fromInfo($info, $now);
    $this->assertEqual([
      'a/2018-01',
      'a/2018-02',
      'a/2018-03',
    ], array_keys($file_pattern->expand('/root')));
  }

  public function testFromInfoIncludingCurrentMonth() {
    $info = [
      'path' => 'a/%Y-%m',
      'retention_period' => new \DateInterval('P3M'),
      'include_current' => TRUE,
    ];
    $now = new \DateTime('2018-04-15');
    $file_pattern = Monthly::fromInfo($info, $now);
    $this->assertEqual([
      'a/2018-01',
      'a/2018-02',
      'a/2018-03',
      'a/2018-04',
    ], array_keys($file_pattern->expand('/root')));
  }

}

<?php

namespace Drupal\campaiginon_csv\test\FilePattern;

use Drupal\campaignion_csv\File;
use Drupal\campaignion_csv\FilePattern\Monthly;

class MonthlyFilePatternTest extends \DrupalUnitTestCase {

  public function testExpandKeysArePaths() {
    $pattern = 'a/%Y-%m';
    $period = new \DatePeriod(new \DateTime('2018-01-01'), new \DateInterval('P1M'), new \DateTime('2018-04-01'));
    $file_pattern = new Monthly($pattern, $period);
    $this->assertEqual([
      'a/2018-01',
      'a/2018-02',
      'a/2018-03',
    ], array_keys(iterator_to_array($file_pattern->expand('/root'))));

  }

  public function testExpandReturnsFiles() {
    $pattern = 'a/%Y-%m';
    $period = new \DatePeriod(new \DateTime('2018-01-01'), new \DateInterval('P1M'), new \DateTime('2018-04-01'));
    $file_pattern = new Monthly($pattern, $period);
    $files = iterator_to_array($file_pattern->expand('/root'));
    $this->assertCount(3, $files);
    $this->assertContainsOnlyInstancesOf(File::class, $files);
  }

  public function testFromInfo() {
    $info = [
      'pattern' => 'a/%Y-%m',
      'retention_period' => new \DateInterval('P3M'),
    ];
    $now = new \DateTime('2018-04-15');
    $file_pattern = Monthly::fromInfo($info, $now);
    $this->assertEqual([
      'a/2018-01',
      'a/2018-02',
      'a/2018-03',
    ], array_keys(iterator_to_array($file_pattern->expand('/root'))));
  }

}

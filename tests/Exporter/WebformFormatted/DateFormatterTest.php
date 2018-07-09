<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Test the date formatter class.
 */
class DateFormatterTest extends \DrupalUnitTestCase {

  /**
   * Test formatting dates.
   */
  public function testFormatTimestamp() {
    date_default_timezone_set('Europe/Vienna');
    $info['date_format'] = '%Y-%m-%dT%H:%M:%S';
    $formatter = DateFormatter::fromInfo($info);
    $this->assertEqual('2018-07-09T17:17:28', $formatter->transform(1531149448));
  }

  /**
   * Test handling of a NULL value.
   */
  public function testFormatEmpty() {
    $info['date_format'] = '%Y-%m-%dT%H:%M:%S';
    $formatter = DateFormatter::fromInfo($info);
    $this->assertEqual('', $formatter->transform(NULL));
  }

}

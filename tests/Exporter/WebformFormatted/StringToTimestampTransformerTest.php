<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Test the date formatter class.
 */
class StringToTimestampTransformerTest extends \DrupalUnitTestCase {

  /**
   * Test formatting dates.
   */
  public function testTransformingWithDefaultConfigValues() {
    date_default_timezone_set('Europe/Vienna');
    $t = StringToTimestampTransformer::fromInfo([]);
    $timestamp = $t->transform('1/2/2013');
    $t_new = new \DateTime();
    $t_new->setTimestamp($timestamp);
    $this->assertEqual('2013-02-01', $t_new->format('Y-m-d'));
    $this->assertEqual(NULL, $t->transform('asdf'));
  }

}

<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Test the date formatter class.
 */
class MapTransformerTest extends \DrupalUnitTestCase {

  /**
   * Test formatting dates.
   */
  public function testTransformingValues() {
    $info['default'] = 'default';
    $info['map']['foo'] = 'bar';
    $t = MapTransformer::fromInfo($info);
    $this->assertEqual('bar', $t->transform('foo'));
    $this->assertEqual('default', $t->transform('baz'));
    $this->assertEqual('default', $t->transform(NULL));
  }

}

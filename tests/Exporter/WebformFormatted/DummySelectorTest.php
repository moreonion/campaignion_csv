<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Test passing values unchanged.
 */
class DummySelectorTest extends \DrupalUnitTestCase {


  /**
   * Test that the value function returns the correct value.
   */
  public function testValue() {
    $node = (object) ['webform' => ['components' => []]];
    $submission = (object) ['data' => []];
    $submission = new Submission($node, $submission);

    foreach(['one', 2, []] as $value) {
      $selector = DummySelector::fromInfo(['value' => $value]);
      $this->assertEqual($value, $selector->value($submission));
    }
  }

}

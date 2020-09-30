<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Test selecting a dictionary of values.
 */
class DictSelectorTest extends \DrupalUnitTestCase {

  /**
   * Test that the value function returns the correct value.
   */
  public function testValue() {
    $node = (object) ['webform' => ['components' => []]];
    $submission = (object) ['data' => []];
    $submission = new Submission($node, $submission);

    $selector = DictSelector::fromInfo([
      'mapping' => [
        'foo' => ['selector' => DummySelector::class, 'value' => '123'],
        'bar' => ['selector' => DummySelector::class, 'value' => NULL],
      ],
    ]);

    $this->assertEqual([
      'foo' => 123,
      'bar' => NULL,
    ], $selector->value($submission));
  }

}

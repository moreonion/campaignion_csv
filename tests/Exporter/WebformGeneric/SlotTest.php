<?php

namespace Drupal\campaignion_csv\Exporter\WebformGeneric;

use Drupal\little_helpers\Webform\Submission;
use Upal\DrupalUnitTestCase;

/**
 * Test behaviour of CSV slots.
 */
class SlotTest extends DrupalUnitTestCase {

  /**
   * Test row length adjustments.
   */
  public function testRowLength() {
    $component = $this->createMock(ComponentExporter::class);
    $component->method('csvHeaders')->willReturn([
      ['one', 'two'],
      [],
      [],
    ]);
    $component->method('csvRow')->will($this->onConsecutiveCalls(
      ['fill'],
      ['cut', 'this', 'down'],
      ['exactly', 'two']
    ));
    $submission = $this->createMock(Submission::class);
    $submission->nid = 0;
    $slot = new Slot('slot_id');
    $slot->setComponent($submission->nid, $component);
    $headers = $slot->headers();

    $row = $slot->row($submission);
    $this->assertEqual(['fill', ''], $row);

    $row = $slot->row($submission);
    $this->assertEqual(['cut', 'this'], $row);

    $row = $slot->row($submission);
    $this->assertEqual(['exactly', 'two'], $row);
  }

}

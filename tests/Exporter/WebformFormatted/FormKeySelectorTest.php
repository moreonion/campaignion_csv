<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Test selecting component values by form key.
 */
class FormKeySelectorTest extends \DrupalUnitTestCase {

  /**
   * Generate a stub submission.
   */
  protected function createSubmission($values) {
    $node = (object) ['webform' => ['components' => [
      1 => ['form_key' => 'one', 'cid' => 1, 'pid' => 0],
      2 => ['form_key' => 'two', 'cid' => 2, 'pid' => 0],
      3 => ['form_key' => 'three', 'cid' => 3, 'pid' => 0],
    ]]];
    $submission = (object) ['data' => [
      1 => [$values[0]],
      2 => [$values[1]],
      3 => [$values[2]],
    ]];
    $submission = new Submission($node, $submission);
    return $submission;
  }

  /**
   * Test that null values are skipped.
   */
  public function testSkipNull() {
    $selector = new FormKeySelector(['one', 'two', 'three'], FALSE);
    $this->assertEqual(NULL, $selector->value($this->createSubmission([NULL, NULL, NULL])));
    $this->assertEqual('bar', $selector->value($this->createSubmission([NULL, NULL, 'bar'])));
    $this->assertEqual('foo', $selector->value($this->createSubmission([NULL, 'foo', 'bar'])));
  }

  /**
   * Test that empty values are skipped if the flag is set.
   */
  public function testSkipEmpty() {
    $selector = new FormKeySelector(['one', 'two', 'three'], TRUE);
    $this->assertEqual(NULL, $selector->value($this->createSubmission([NULL, NULL, ''])));
    $this->assertEqual('bar', $selector->value($this->createSubmission([NULL, '', 'bar'])));
    $this->assertEqual('foo', $selector->value($this->createSubmission(['', 'foo', 'bar'])));
  }

}

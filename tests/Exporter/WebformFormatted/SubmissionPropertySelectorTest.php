<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Test selecting properties of a submission.
 */
class SubmissionPropertySelectorTest extends \DrupalUnitTestCase {

  /**
   * Test that the value function returns the correct value.
   */
  public function testUsername() {
    $node = (object) ['webform' => ['components' => []]];
    $submission = (object) ['data' => [], 'uid' => 0];
    $submission = new Submission($node, $submission);

    $user = new SubmissionPropertySelector('user.name');
    $this->assertEqual('', $user->value($submission));
  }

  /**
   * Test that getting overloaded properties of a nested payment works.
   */
  public function testPaymentStatus() {
    $node = (object) ['webform' => ['components' => []]];
    $submission = (object) ['data' => []];
    $payment = new \Payment([
      'pid' => 1,
    ]);
    $submission->payments = [1 => $payment];
    $submission = new Submission($node, $submission);

    $status = new SubmissionPropertySelector('payment.status.status');
    $this->assertEqual('payment_status_new', $status->value($submission));
  }

}

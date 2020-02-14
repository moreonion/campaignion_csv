<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Test selecting properties of a submission’s (first) payment.
 */
class PaymentPropertySelectorTest extends \DrupalUnitTestCase {

  /**
   * Test that the value function returns the correct value.
   */
  public function testValue() {
    $node = (object) ['webform' => ['components' => []]];
    $submission = (object) ['data' => []];
    $payment = (object) [
      'pid' => 1,
      'method_data' => ['account' => '12345'],
      'method' => (object) ['name' => 'direct_debit'],
    ];
    $submission->payments = [1 => $payment];
    $submission = new Submission($node, $submission);

    $one = PaymentPropertySelector::fromInfo([]);
    $this->assertEqual($payment->pid, $one->value($submission));

    $two = new PaymentPropertySelector('method_data.account');
    $this->assertEqual($payment->method_data['account'], $two->value($submission));

    $other = new PaymentPropertySelector('method.name');
    $this->assertEqual($payment->method->name, $other->value($submission));
  }

}

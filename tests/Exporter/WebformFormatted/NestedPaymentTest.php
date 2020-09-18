<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Upal\DrupalUnitTestCase;

/**
 * Test for the nested data structure wrapper for payments.
 */
class NestedPaymentTest extends DrupalUnitTestCase {

  /**
   * Test recurrence calculation for empty payments.
   */
  public function testRecurrenceWithEmptyPayment() {
    $payment = new \Payment();
    $nested = new NestedPayment($payment);
    $this->assertEqual('', $nested->recurrence());
  }

  /**
   * Test recurrence calculation with monthly payments.
   */
  public function testRecurrenceWithMonthlyPayment() {
    $payment = new \Payment();
    $item = new \PaymentLineItem();
    $item->recurrence = (object) [
      'interval_unit' => 'monthly',
    ];
    $payment->setLineItem($item);
    $nested = new NestedPayment($payment);
    $this->assertEqual('P1M', $nested->recurrence());

    $item->recurrence->interval_value = 3;
    $this->assertEqual('P3M', $nested->recurrence());
  }

  /**
   * Test recurrence calculation with yearly payments.
   */
  public function testRecurrenceWithYearlyPayment() {
    $payment = new \Payment();
    $item = new \PaymentLineItem();
    $item->recurrence = (object) [
      'interval_unit' => 'yearly',
    ];
    $payment->setLineItem($item);
    $nested = new NestedPayment($payment);
    $this->assertEqual('P1Y', $nested->recurrence());

    $item->recurrence->interval_value = 3;
    $this->assertEqual('P3Y', $nested->recurrence());
  }

  /**
   * Test recurrence calculation with a mixed payment.
   */
  public function testRecurrenceWithMixedPayment() {
    $payment = new \Payment();
    $payment->setLineItem(new \PaymentLineItem(['name' => 'one-time']));
    $item = new \PaymentLineItem(['name' => 'monthly']);
    $item->recurrence = (object) [
      'interval_unit' => 'monthly',
    ];
    $payment->setLineItem($item);
    $nested = new NestedPayment($payment);
    $this->assertEqual('mixed', $nested->recurrence());
  }

}

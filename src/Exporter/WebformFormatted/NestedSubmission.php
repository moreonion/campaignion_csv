<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Nested data wrapper for webform submissions.
 */
class NestedSubmission extends NestedData {

  /**
   * Fetch the first (and most likely) only payment object.
   */
  public function payment() {
    $payments = $this->data->payments ?? [];
    $payment = reset($payments);
    return $payment ? new NestedPayment($payment) : $payment;
  }

  /**
   * Fetch the submitting Drupal user.
   */
  public function user() {
    return user_load($this->data->uid ?? 0);
  }

}

<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Nested data wrapper for payments.
 */
class NestedPayment extends NestedData {

  /**
   * Fetch the first current payment status.
   */
  public function status() {
    return $this->data->getStatus();
  }

  /**
   * Get the total amount of a payment.
   */
  public function totalAmount() {
    return $this->data->totalAmount(TRUE);
  }

  /**
   * Figure out the recurrence for the entire payment (if possible).
   */
  public function recurrence() {
    $intervals = [];
    $iso_by_interval = [
      'monthly' => 'M',
      'yearly' => 'Y',
    ];
    foreach ($this->data->getLineItems() as $item) {
      if (($interval = $item->recurrence->interval_unit ?? NULL)) {
        $value = $item->recurrence->interval_value ?? 1;
        $unit = $iso_by_interval[$interval] ?? $interval;
        $intervals["P{$value}{$unit}"] = TRUE;
      }
      else {
        $intervals['once'] = TRUE;
      }
    }
    if (!$intervals) {
      // No line-items.
      return '';
    }
    if (count($intervals) > 1) {
      return 'mixed';
    }
    return array_keys($intervals)[0];
  }

}

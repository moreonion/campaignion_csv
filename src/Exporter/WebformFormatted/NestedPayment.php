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

}

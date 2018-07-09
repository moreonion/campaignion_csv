<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Default formatter: Cast to a string.
 */
class NoopFormatter {

  /**
   * Create new instance from an info array.
   */
  public static function fromInfo(array $info) {
    return new static();
  }

  /**
   * Return value as string.
   */
  public function format($value) {
    return (string) $value;
  }

}

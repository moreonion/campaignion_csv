<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Format values as a CSV cell.
 */
interface FormatterInterface {

  /**
   * Create a new instance from an info-array.
   */
  public static function fromInfo(array $info);

  /**
   * Format a value as string.
   *
   * Implementing classes are allowed to make assumptions on the type of values
   * they get passed. They always must be able to handle NULL.
   *
   * @param mixed $value
   *   The value to be formatted.
   *
   * @return string
   *   The formatted value.
   */
  public function transform($value);

}

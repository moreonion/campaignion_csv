<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Format a unix timestamp.
 */
class DateFormatter implements TransformerInterface {

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - date_format: The date format as accepted by `strftime()`.
   */
  public static function fromInfo(array $info) {
    $info += ['date_format' => '%Y-%m-%d'];
    return new static($info['date_format']);
  }

  /**
   * Create a new instance.
   *
   * @param string $format
   *   The date format as accepted by `strftime()`.
   */
  public function __construct($format) {
    $this->format = $format;
  }

  /**
   * Create a formtted string from the timestamp.
   *
   * @param int $value
   *   The timestamp.
   *
   * @return string
   *   The formatted date/time or an empty string if an empty value was given.
   */
  public function transform($value) {
    if ($value) {
      return strftime($this->format, $value);
    }
    return '';
  }

}

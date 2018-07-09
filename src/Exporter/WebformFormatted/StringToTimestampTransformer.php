<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Convert a string formatted date to a timestamp.
 */
class StringToTimestampTransformer implements TransformerInterface {

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - parse_formats: An array of possible date formats to try.
   */
  public static function fromInfo(array $info) {
    $info += [
      'parse_formats' => ['d/m/Y', 'j/n/Y'],
    ];
    return new static($info['parse_formats']);
  }

  /**
   * Create a new instance.
   *
   * @param string[] $formats
   *   An array of possible date formats to try.
   */
  public function __construct(array $formats) {
    $this->formats = $formats;
  }

  /**
   * Parse a date string and return it’s unix timestamp.
   *
   * @param string $value
   *   The date string.
   *
   * @return int|null
   *   The unix timestamp or NULL if the string matched no format.
   */
  public function transform($value) {
    foreach ($this->formats as $format) {
      if ($d = \DateTime::createFromFormat($format, $value)) {
        return $d->getTimestamp();
      }
    }
    return NULL;
  }

}

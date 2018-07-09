<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Transform values.
 */
interface TransformerInterface {

  /**
   * Create a new instance from an info-array.
   */
  public static function fromInfo(array $info);

  /**
   * Transform a value.
   *
   * Implementing classes are allowed to make assumptions on the type of values
   * they get passed. They always must be able to handle NULL.
   *
   * @param mixed $value
   *   The value to be transformed.
   *
   * @return mixed
   *   The new value.
   */
  public function transform($value);

}

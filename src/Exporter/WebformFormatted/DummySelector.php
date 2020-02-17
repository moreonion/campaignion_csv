<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Pass a fixed value.
 */
class DummySelector implements SelectorInterface {

  protected $value;

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - value: The value for this column.
   */
  public static function fromInfo(array $info) {
    $info += ['value' => NULL];
    return new static($info['value']);
  }

  /**
   * Create a new instance.
   *
   * @param mixed $value
   *   The value for this column.
   */
  public function __construct($value) {
    $this->value = $value;
  }

  /**
   * Return the initial value.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The webform submission to be ignored.
   *
   * @return mixed
   *   The initial value.
   */
  public function value(Submission $submission) {
    return $this->value;
  }

}

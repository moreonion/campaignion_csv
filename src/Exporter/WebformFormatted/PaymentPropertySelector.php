<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Select one of the submission properties.
 */
class PaymentPropertySelector implements SelectorInterface {

  protected $property;

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following propertys are in use:
   *   - property: Name of the payment property. Also works with paths like
   *     `method_data.account`.
   */
  public static function fromInfo(array $info) {
    $info += [
      'property' => 'pid',
    ];
    return new static($info['property']);
  }

  /**
   * Create a new instance.
   *
   * @param string $property
   *   Name of the payment property. Also works with period seperated paths.
   */
  public function __construct($property) {
    $this->property = $property;
  }

  /**
   * Get property value from a submission.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The webform submission.
   *
   * @return mixed
   *   The property value.
   */
  public function value(Submission $submission) {
    // Get the data from the first paymethod select component.
    $data = reset($submission->payments);
    foreach (explode('.', $this->property) as $prop) {
      $data = $data->{$prop} ?? $data[$prop] ?? NULL;
    }
    return $data;
  }

}

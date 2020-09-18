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
    // Don’t call reset($submission->payments) directly. This doesn’t work on an
    // overloaded property.
    $payments = $submission->payments ?? [];
    if ($payment = reset($payments)) {
      return static::deepGet($payment, explode('.', $this->property), [
        'status' => $payment->getStatus(),
      ]);
    }
    return NULL;
  }

  /**
   * Helper function that recursively resolves nested data structures.
   */
  protected static function deepGet($data, array $path_parts, array $special = []) {
    $part = array_shift($path_parts);
    if (array_key_exists($part, $special)) {
      // Values might be NULL.
      $data = $special[$part];
    }
    else {
      if (is_array($data)) {
        $data = $data[$part] ?? NULL;
      }
      else {
        $data = $data->{$part} ?? NULL;
      }
    }
    if ($path_parts && $data) {
      // No need to use static:: when calling the method itself: Overriding the
      // method means the call is overridden too.
      // Special values are only valid for the top layer.
      return self::deepGet($data, $path_parts);
    }
    else {
      return $data;
    }
  }

}

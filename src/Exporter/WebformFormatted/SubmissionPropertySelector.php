<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Select one of the submission properties.
 */
class SubmissionPropertySelector implements SelectorInterface {

  protected $property;

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - propery: Name of the submission property. Also works with paths like
   *     `ǹode.language`.
   */
  public static function fromInfo(array $info) {
    $info += ['property' => 'sid'];
    return new static($info['property']);
  }

  /**
   * Create a new instance.
   *
   * @param string $property
   *   Name of the submission property. Also works with period seperated paths.
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
    $payments = $submission->payments ?? [];
    return static::deepGet($submission, explode('.', $this->property), [
      'payment' => reset($payments),
      'user' => user_load($submission->uid ?? 0),
    ]);
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
      $data = $data->$part ?? NULL;
    }
    if ($path_parts) {
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

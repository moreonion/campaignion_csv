<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Select values from a webform submission using the `form_key`.
 */
class FormKeySelector {

  protected $keys;

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - keys: An array of form_keys to try until a non-NULL value is found.
   */
  public static function fromInfo(array $info) {
    $info += ['keys' => []];
    return new static($info['keys']);
  }

  /**
   * Create a new instance.
   *
   * @param string[] $keys
   *   An array of form_keys to try until a non-NULL value is found.
   */
  public function __construct(array $keys) {
    $this->keys = $keys;
  }

  /**
   * Get the value for a specific submission.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The webform submission.
   *
   * @return mixed
   *   The first non-NULL value for this submission or NULL if none was found.
   */
  public function value(Submission $submission) {
    foreach ($this->keys as $key) {
      $value = $submission->valueByKey($key);
      if (!is_null($value)) {
        return $value;
      }
    }
    return NULL;
  }

}

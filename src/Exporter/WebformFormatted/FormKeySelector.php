<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Select values from a webform submission using the `form_key`.
 */
class FormKeySelector implements SelectorInterface {

  protected $keys;
  protected $skipEmpty;

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - keys: An array of form_keys to try until a non-NULL value is found.
   *   - skip_empty: A flag to control whether empty values are ignored.
   */
  public static function fromInfo(array $info) {
    $info += ['keys' => [], 'skip_empty' => FALSE];
    return new static($info['keys'], $info['skip_empty']);
  }

  /**
   * Create a new instance.
   *
   * @param string[] $keys
   *   An array of form_keys to try until a non-NULL value is found.
   * @param bool $skip_empty
   *   Flag to control whether empty values are ignored (TRUE), or not (FALSE).
   */
  public function __construct(array $keys, bool $skip_empty) {
    $this->keys = $keys;
    $this->skipEmpty = $skip_empty;
  }

  /**
   * Get the value for a specific submission.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The webform submission.
   *
   * @return mixed
   *   The first value found for this submission or NULL if none was found.
   */
  public function value(Submission $submission) {
    foreach ($this->keys as $key) {
      $value = $submission->valueByKey($key);
      if (is_null($value) || $this->skipEmpty && empty($value)) {
        continue;
      }
      return $value;
    }
    return NULL;
  }

}

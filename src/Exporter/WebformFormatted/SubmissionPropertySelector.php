<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Select one of the submission properties.
 */
class SubmissionPropertySelector {

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
    $property = $this->property;
    if (substr($property, 0, 5) == 'node.') {
      $property = substr($property, 5);
      return $submission->node->{$property};
    }
    return $submission->{$property};
  }

}

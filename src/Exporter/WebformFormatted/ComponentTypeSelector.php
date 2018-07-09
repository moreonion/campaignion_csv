<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Select webform submission values based on their column-type.
 */
class ComponentTypeSelector implements SelectorInterface {

  protected $type;

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - component_type: A string describing a component-type.
   */
  public static function fromInfo(array $info) {
    $info += ['component_type' => NULL];
    return new static($info['component_type']);
  }

  /**
   * Create a new instance.
   *
   * @param string $type
   *   The component type to select.
   */
  public function __construct($type) {
    $this->type = $type;
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
    foreach ($submission->webform->componentsByType($this->type) as $cid => $component) {
      $value = $submission->valueByCid($cid);
      if (!is_null($value)) {
        return $value;
      }
    }
    return NULL;
  }

}

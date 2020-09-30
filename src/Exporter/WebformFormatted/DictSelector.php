<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Select a dictionary of values.
 */
class DictSelector implements SelectorInterface {

  /**
   * Associative array of nested columns.
   *
   * @var \Drupal\campaignion_csv\Exporter\WebformFormatted\Column[]
   */
  protected $columns;

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - mapping: An associative array of nested selectors.
   */
  public static function fromInfo(array $info) {
    $info += ['mapping' => []];
    $columns = [];
    foreach ($info['mapping'] as $key => $i) {
      $i += ['label' => $i];
      $columns[$key] = Column::fromInfo($i);
    }
    return new static($columns);
  }

  /**
   * Create a new instance.
   *
   * @param \Drupal\campaignion_csv\Exporter\WebformFormatted\Column[] $columns
   *   Associative array of nested columns.
   */
  public function __construct(array $columns) {
    $this->columns = $columns;
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
    return array_map(function (Column $column) use ($submission) {
      return $column->value($submission);
    }, $this->columns);
  }

}

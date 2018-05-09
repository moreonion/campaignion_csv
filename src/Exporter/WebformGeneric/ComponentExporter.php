<?php

namespace Drupal\campaignion_csv\Exporter\WebformGeneric;

use Drupal\little_helpers\Webform\Submission;

/**
 * Exporter for a single webform component.
 */
class ComponentExporter {

  /**
   * Create a new exporter.
   *
   * @param array $component
   *   The webform component array.
   * @param array $options
   *   The webform download options for the component’s node.
   */
  public function __construct(array $component, array $options) {
    $this->component = $component;
    $this->options = $options;
  }

  /**
   * Generate the CSV headers for this component.
   */
  public function csvHeaders() {
    return (array) webform_component_invoke($this->component['type'], 'csv_headers', $this->component, $this->options);
  }

  /**
   * Generate a CSV cells for this component for a submission.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The submission that’s being exported.
   *
   * @return string[]
   *   The cells for this submission.
   */
  public function csvRow(Submission $submission) {
    $c = $this->component;
    $data = webform_component_invoke($c['type'], 'csv_data', $c, $this->options, $submission->valuesByCid($c['cid']));
    return is_array($data) ? $data : [$data];
  }

  /**
   * Get the `slot_id` for this component.
   *
   * @return string
   *   The slot id.
   */
  public function slotId() {
    return $this->component['form_key'];
  }

}

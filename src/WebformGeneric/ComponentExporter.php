<?php

namespace Drupal\campaignion_csv\WebformGeneric;

use Drupa\little_helpers\Webform\Submission;

class ComponentExporter {

  public function __construct($component, $options) {
    $this->component = $component;
    $this->options = $options;
  }

  public function csvHeaders() {
    return (array) webform_component_invoke($this->component['type'], 'csv_headers', $this->component, $this->options);
  }

  public function csvRow($submission) {
    $c = $this->component;
    $data = webform_component_invoke($c['type'], 'csv_data', $c, $this->options, $submission->valuesByCid($c['cid']));
    return is_array($data) ? $data : [$data];
  }

  public function slotId() {
    return $this->component['form_key'];
  }

}


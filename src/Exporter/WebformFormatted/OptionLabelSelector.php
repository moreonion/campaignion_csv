<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Select the label (instead of the value) of a webform select component.
 */
class OptionLabelSelector extends FormKeySelector {

  /**
   * {@inheritdoc}
   */
  public function value(Submission $submission) {
    foreach ($this->keys as $key) {
      $value = $submission->valueByKey($key);
      if (!is_null($value)) {
        $component = $submission->webform->componentByKey($key);
        return $this->getLabel($component, $value);
      }
    }
    return NULL;
  }

  /**
   * Get the label for a value.
   *
   * @param array $component
   *   The currently read webform component.
   * @param string $value
   *   The value for which the label should be found.
   *
   * @return string
   *   The label for the given value.
   */
  protected function getLabel(array $component, $value) {
    $options = webform_component_invoke($component['type'], 'options', $component, TRUE);
    if (isset($options[$value])) {
      return $options[$value];
    }
    return $value;
  }

}

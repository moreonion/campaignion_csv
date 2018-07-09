<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Data selection and output configuraton for one column in the CSV.
 */
class Column {

  /**
   * Create new instance from an info-array.
   *
   * @param array $info
   *   Configuration as specified in the campaignion_csv_info().
   */
  public static function fromInfo(array $info) {
    $info += [
      'selector' => FormKeySelector::class,
      'transformers' => [],
      'formatter' => NoopFormatter::class,
    ];
    $class = $info['selector'];
    $selector = $class::fromInfo($info);
    $transformers = [];
    foreach ($info['transformers'] as $tinfo) {
      $class = $tinfo['class'];
      $transformers[] = $class::fromInfo($tinfo);
    }
    $class = $info['formatter'];
    $formatter = $class::fromInfo($info);
    return new static($info['label'], $selector, $transformers, $formatter);
  }

  /**
   * Create a new instance.
   *
   * @param string $label
   *   Column header for this column.
   * @param \Drupal\campaignion_csv\Exporter\WebformFormatted\SelectorInterface $selector
   *   Value selector.
   * @param \Drupal\campaignion_csv\Exporter\WebformFormatted\TransformerInterface[] $transformers
   *   Value transformers.
   * @param \Drupal\campaignion_csv\Exporter\WebformFormatted\FormatterInterface $formatter
   *   Formatter for this cell.
   */
  public function __construct($label, $selector, array $transformers, $formatter) {
    $this->label = $label;
    $this->selector = $selector;
    $this->transformers = $transformers;
    $this->formatter = $formatter;
  }

  /**
   * Get the cell value for this column and submission.
   *
   * Each cell value is processed by a pipe of objects:
   *   - The selector reads the value from the submission object.
   *   - An arbitrary number of transformers modify the values usually
   *     transforming it into a standardised format. (optional)
   *   - The formatter then creates the output for the CSV.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The webform submission to read the data from.
   *
   * @return string
   *   The value of for this cell.
   */
  public function value(Submission $submission) {
    $value = $this->selector->value($submission);
    foreach ($this->transformers as $transformer) {
      $value = $transformer->transform($value);
    }
    return $this->formatter->format($value);
  }

}

<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Reads a value from a submission object.
 */
interface SelectorInterface {

  /**
   * Create a new instance from an info-array.
   */
  public static function fromInfo(array $info);

  /**
   * Read value from a submission object.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The webform submission to read from.
   *
   * @return mixed
   *   The extracted value.
   */
  public function value(Submission $submission);

}

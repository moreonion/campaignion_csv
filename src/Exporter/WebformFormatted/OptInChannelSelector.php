<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

/**
 * Select webform opt-in submission values based on their channel.
 */
class OptInChannelSelector implements SelectorInterface {

  protected $channel;

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - channel: The selected channel.
   */
  public static function fromInfo(array $info) {
    $info += ['channel' => 'email'];
    return new static($info['channel']);
  }

  /**
   * Create a new instance.
   *
   * @param string $channel
   *   The opt-in channel to select.
   */
  public function __construct($channel) {
    $this->channel = $channel;
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
    return $submission->opt_in->canonicalValue($this->channel, TRUE);
  }

}

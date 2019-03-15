<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\little_helpers\Webform\Submission;

require_once drupal_get_path('module', 'webform') . '/includes/webform.components.inc';

/**
 * Test selecting opt-in values by channel.
 */
class OptInChannelSelectorTest extends \DrupalUnitTestCase {

  /**
   * Generate a new opt_in component according to the parameters.
   */
  protected function getComponent($extra, $component = []) {
    $component['type'] = 'opt_in';
    $component['extra'] = $extra;
    webform_component_defaults($component);
    return $component;
  }

  /**
   * Test that the value function returns the correct value.
   */
  public function testValue() {
    $node = (object) ['webform' => []];
    $node->webform['components'] = [
      1 => $this->getComponent(['channel' => 'one'], ['cid' => 1, 'form_key' => 'one']),
      2 => $this->getComponent(['channel' => 'two'], ['cid' => 2, 'form_key' => 'two']),
      3 => $this->getComponent(['channel' => 'one'], ['cid' => 3, 'form_key' => 'three']),
    ];
    $submission = (object) ['data' => []];
    $submission->data = [
      1 => ['checkbox:opt-in'],
      2 => [],
      3 => ['radios:opt-out'],
    ];
    $submission = new Submission($node, $submission);

    $one = new OptInChannelSelector('one');
    $this->assertEqual('opt-in', $one->value($submission));

    $two = new OptInChannelSelector('two');
    $this->assertEqual('', $two->value($submission));

    $other = new OptInChannelSelector('other');
    $this->assertEqual('', $other->value($submission));
  }

}

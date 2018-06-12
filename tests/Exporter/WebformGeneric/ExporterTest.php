<?php

namespace Drupal\campaignion_csv\Exporter\WebformGeneric;

use Drupal\campaignion_csv\Timeframe;
use Drupal\campaignion_csv\Files\CsvFile;

require_once drupal_get_path('module', 'webform') . '/includes/webform.components.inc';
require_once drupal_get_path('module', 'webform') . '/includes/webform.submissions.inc';

/**
 * Integration test for the generic webform exporter.
 */
class ExporterTest extends \DrupalUnitTestCase {

  /**
   * Prepare webform with an email and textfield component plus one submission.
   */
  public function setUp() {
    parent::setUp();
    $this->node = (object) [
      'type' => 'webform',
      'title' => 'ExporterTest',
    ];
    node_object_prepare($this->node);
    $this->node->webform['components'][1] = [
      'type' => 'email',
      'name' => 'Email',
      'form_key' => 'email',
    ];
    $this->node->webform['components'][2] = [
      'type' => 'textfield',
      'name' => 'First name',
      'form_key' => 'first_name',
    ];
    foreach ($this->node->webform['components'] as &$component) {
      webform_component_defaults($component);
    }
    node_save($this->node);
    $form_state['values']['submitted'] = [
      1 => 'test@example.com',
      2 => 'Foo',
    ];
    $this->submission = webform_submission_create($this->node, $GLOBALS['user'], $form_state);
    webform_submission_insert($this->node, $this->submission);
    $this->path = tempnam(sys_get_temp_dir(), 'webform-generic-test');
  }

  /**
   * Delete the webform node.
   */
  public function tearDown() {
    node_delete($this->node->nid);
    unlink($this->path);
  }

  /**
   * Test exporting the submissions.
   */
  public function testWriteTo() {
    $start = (new \DateTimeImmutable())->modify('-2 hours');
    $length = new \DateInterval('PT4H');
    $info['timeframe'] = new Timeframe($start, $length);
    $info['actions'] = TRUE;

    $exporter = Exporter::fromInfo($info);
    $file = new CsvFile($this->path, 'w');
    $exporter->writeTo($file);

    $read_file = new CsvFile($this->path, 'r');
    $rows = [];
    while ($row = $read_file->fgetcsv()) {
      $rows[] = $row;
    }
    $this->assertCount(6, $rows);
    $this->assertEqual('Email', $rows[2][9]);
    $this->assertEqual('First name', $rows[2][10]);

    $this->assertEqual('email', $rows[3][9]);
    $this->assertEqual('first_name', $rows[3][10]);

    $this->assertEqual('test@example.com', $rows[4][9]);
    $this->assertEqual('Foo', $rows[4][10]);
  }

}
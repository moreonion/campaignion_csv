<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\campaignion_csv\Timeframe;
use Drupal\campaignion_csv\Files\CsvFile;

require_once drupal_get_path('module', 'webform') . '/includes/webform.components.inc';
require_once drupal_get_path('module', 'webform') . '/includes/webform.submissions.inc';

/**
 * Integration test for the formatted webform exporter.
 */
class ExporterTest extends \DrupalUnitTestCase {

  /**
   * Prepare webform with an email and textfield component plus one submission.
   */
  public function setUp(): void {
    parent::setUp();
    $this->node = (object) [
      'type' => 'webform',
      'title' => 'ExporterTest',
      'language' => 'test',
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
    $this->node->webform['components'][3] = [
      'type' => 'select',
      'name' => 'Options',
      'form_key' => 'opt',
      'extra' => [
        'items' => "1|One\n",
      ],
    ];
    foreach ($this->node->webform['components'] as $cid => &$component) {
      $component['cid'] = $cid;
      webform_component_defaults($component);
    }
    node_save($this->node);
    $form_state['values']['submitted'] = [
      1 => 't@e.com',
      2 => 'Foo',
      3 => '1',
    ];
    $this->submission = webform_submission_create($this->node, $GLOBALS['user'], $form_state);
    webform_submission_insert($this->node, $this->submission);
    $this->path = tempnam(sys_get_temp_dir(), 'webform-generic-test');
  }

  /**
   * Delete the webform node.
   */
  public function tearDown(): void {
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
    $info['languages'] = 'test';
    $info['columns']['email'] = ['keys' => ['email']];
    $info['columns']['submitted'] = [
      'selector' => SubmissionPropertySelector::class,
      'property' => 'submitted',
      'transformers' => [
        [
          'class' => DateFormatter::class,
          'date_format' => '%Y-%m-%d %H:%M:%S',
        ],
      ],
    ];
    $info['columns']['email2'] = [
      'selector' => ComponentTypeSelector::class,
      'component_type' => 'email',
    ];
    $info['columns']['opt'] = [
      'keys' => ['opt'],
      'selector' => OptionLabelSelector::class,
    ];

    $exporter = Exporter::fromInfo($info);
    $file = new CsvFile($this->path, 'w');
    $exporter->writeTo($file);

    $read_file = new CsvFile($this->path, 'r');
    $rows = [];
    while ($row = $read_file->fgetcsv()) {
      $rows[] = $row;
    }

    $this->assertCount(3, $rows);
    $this->assertEqual(['email', 'submitted', 'email2', 'opt'], $rows[0]);
    $t = strftime('%Y-%m-%d %H:%M:%S', $this->submission->submitted);
    $this->assertEqual(['t@e.com', $t, 't@e.com', 'One'], $rows[1]);
    $this->assertEqual([NULL], $rows[2]);
  }

}

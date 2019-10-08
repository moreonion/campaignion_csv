<?php

namespace Drupal\campaignion_csv\Exporter\WebformGeneric;

use Drupal\little_helpers\Webform\Submission;
use Drupal\campaignion_action\Loader;
use Drupal\campaignion_csv\Files\CsvFileInterface;
use Drupal\campaignion_csv\Timeframe;

$webform_path = drupal_get_path('module', 'webform');
require_once $webform_path . '/includes/webform.export.inc';
require_once $webform_path . '/includes/webform.report.inc';

/**
 * A generic multi-node webform submission exporter.
 *
 * Webform components are grouped into slots. All components in a slot share
 * a set of columns in the CSV file.
 */
class Exporter {

  /**
   * Get the content-types based on action types.
   *
   * @param bool $actions
   *   Whether to include non-donation actions.
   * @param bool $donations
   *   Whether to include donation actions.
   *
   * @return string[]
   *   Machine names of the content types matching the criteria.
   */
  public static function getContentTypes($actions = TRUE, $donations = FALSE) {
    $action_types = Loader::instance()->allTypes();
    $types = [];
    foreach (webform_node_types() as $type) {
      if (!isset($action_types[$type]) || !$action_types[$type]->isDonation()) {
        if ($actions) {
          $types[] = $type;
        }
      }
      else {
        if ($donations) {
          $types[] = $type;
        }
      }
    }
    return $types;
  }

  /**
   * Create new instance based on timeframe and info.
   *
   * @param array $info
   *   The exporter info array as defined campaignion_csv_info().
   */
  public static function fromInfo(array $info) {
    $info += [
      'actions' => TRUE,
      'donations' => FALSE,
    ];
    $types = static::getContentTypes($info['actions'], $info['donations']);
    return new static($info['timeframe'], $types);
  }

  /**
   * Create a new instance.
   *
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   Export submissions within this timeframe.
   * @param string[] $types
   *   Limit submissions to these node types (bundles).
   */
  public function __construct(Timeframe $timeframe, array $types) {
    $this->timeframe = $timeframe;
    $this->types = $types;
    $this->nodes = $this->getNodes();
  }

  /**
   * Generator: Iterate over all submissions within the timeframe.
   */
  protected function getSubmissions() {
    $nids = array_keys($this->nodes);
    if (!$nids) {
      return;
    }
    list($start, $end) = $this->timeframe->getTimeStamps();
    $result = db_select('webform_submissions', 's')
      ->fields('s', ['nid', 'sid'])
      ->condition('nid', $nids)
      ->condition('submitted', [$start, $end - 1], 'BETWEEN')
      ->orderBy('sid')
      ->execute();
    foreach ($result as $row) {
      yield Submission::load($row->nid, $row->sid);
      drupal_static_reset('webform_get_submission');
    }
  }

  /**
   * Get all nodes with submissions in the specified timeframe.
   *
   * @return object[]
   *   Arrays keyed by their nids.
   */
  protected function getNodes() {
    list($start, $end) = $this->timeframe->getTimeStamps();
    $q = db_select('webform_submissions', 's');
    $q->innerJoin('node', 'n', 'n.nid = s.nid');
    $nids = $q->fields('n', ['nid'])
      ->condition('n.type', $this->types)
      ->condition('submitted', [$start, $end - 1], 'BETWEEN')
      ->groupBy('nid')
      ->orderBy('nid')
      ->execute()
      ->fetchCol();
    return entity_load('node', $nids);
  }

  /**
   * Get the submission information data for a submission.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The webform submission.
   * @param array $options
   *   The download options for the submission’s node.
   * @param int $row_count
   *   The number of the current row.
   * @param array $submission_info_cols
   *   Data from an earlier call to
   *   webform_results_download_submission_information().
   *
   * @return array
   *   Submission information data for the submission keyed by token.
   */
  protected function submissionInformationData(Submission $submission, array $options, $row_count, array $submission_info_cols) {
    $context = [
      'submission' => $submission,
      'options' => $options,
      'serial_start' => 0,
      'row_count' => $row_count,
    ];
    // Check whether this is a patched version of webform.
    $patched = !isset(webform_theme()['webform_results_table_header']);
    if ($patched) {
      $data = module_invoke_all('webform_results_download_submission_information_data', $submission, $options, 0, $row_count);
      drupal_alter('webform_results_download_submission_information_data', $data, $context);
    }
    else {
      foreach (array_keys($submission_info_cols) as $token) {
        $cell = module_invoke_all('webform_results_download_submission_information_data', $token, $submission, $options, 0, $row_count);
        drupal_alter('webform_results_download_submission_information_data', $cell, $context);
        // Merge multiple values into one if more than one module responds.
        $data['token'] = implode(', ', $cell);
      }
    }
    return $data;
  }

  /**
   * Get webform download options for all nodes.
   *
   * @return array
   *   Array of download options keyed by `$node->nid`.
   */
  protected function getDownloadOptions() {
    $options = [];
    foreach ($this->nodes as $node) {
      $options[$node->nid] = [
        'type' => 'download',
        'select_format' => 'compact',
        'select_keys' => TRUE,
        'multiple_nodes' => TRUE,
        'components' => NULL,
      ] + webform_results_download_default_options($node, 'delimited');
    }
    return $options;
  }

  /**
   * Write submission files to the CsvFile.
   */
  public function writeTo(CsvFileInterface $file) {
    $slots = [];
    $options_by_nid = $this->getDownloadOptions();
    $submission_info_cols = [];

    foreach ($this->nodes as $node) {
      $options = $options_by_nid[$node->nid];
      $submission_info_cols += webform_results_download_submission_information($node, $options);

      foreach ($node->webform['components'] as $component) {
        if (webform_component_feature($component['type'], 'csv')) {
          $component_exporter = new ComponentExporter($component, $options);
          $slot_id = $component_exporter->slotId();
          if (!isset($slots[$slot_id])) {
            $slots[$slot_id] = new Slot($slot_id);
          }
          $slots[$slot_id]->setComponent($node->nid, $component_exporter);
        }
      }
    }

    $slot_headers = [];
    foreach ($slots as $slot_id => $slot) {
      $slot_headers[$slot_id] = $slot->headers();
    }

    for ($row_num = 0; $row_num <= 3; $row_num++) {

      if ($row_num == 2) {
        $row = array_map(function ($x) {
          return is_array($x) ? $x['title'] : $x;
        }, $submission_info_cols);
      }
      else {
        $row = array_fill(0, count($submission_info_cols), '');
      }

      foreach ($slot_headers as $headers) {
        $row = array_merge($row, $headers[$row_num]);
      }
      $file->writeRow($row);
    }

    $row_count = 0;
    foreach ($this->getSubmissions() as $submission) {
      $nid = $submission->nid;
      $options = $options_by_nid[$nid];
      $row = [];

      // Add submission information.
      $data = $this->submissionInformationData($submission, $options, $row_count, $submission_info_cols);
      foreach (array_keys($submission_info_cols) as $token) {
        $row[] = isset($data[$token]) ? $data[$token] : '';
      }

      // Add submission data.
      foreach ($slots as $slot) {
        $row = array_merge($row, $slot->row($submission));
      }
      $file->writeRow($row);
      $row_count++;
    }
  }

}

<?php

namespace Drupal\campaignion_csv\WebformGeneric;

use Drupal\little_helpers\Webform\Submission;
use Drupal\campaignion_action\Loader;
use Drupal\campaignion_csv\CsvWriter;
use Drupal\campaignion_csv\Timeframe;

$webform_path = drupal_get_path('module', 'webform');
require_once $webform_path . '/includes/webform.export.inc';
require_once $webform_path . '/includes/webform.report.inc';

class Exporter {

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

  public function fromInfo(Timeframe $timeframe, array $info) {
    $info += [
      'actions' => TRUE,
      'donations' => FALSE,
    ];
    $types = static::getContentTypes($info['actions'], $info['donations']);
    return new static($timeframe, $types);
  }

  public function __construct(Timeframe $timeframe, array $types) {
    $this->timeframe = $timeframe;
    $this->types = $types;
    $this->nodes = $this->getNodes();
  }

  protected function normalizeRowLength($rows, $fill = []) {
    $count = function($x) {
      return count($x);
    };
    $num_cols = max(array_map($count, $rows));

    $fill_row = function($x) use ($num_cols, $fill) {
      $c = count($x);
      return array_merge($x, array_fill(0, $num_cols - $c, $fill));
    };
    return array_map($fill_row, $rows);
  }


  protected function getSubmissions() {
    $nids = array_keys($this->nodes);
    list($start, $end) = $this->timeframe->getTimeStamps();
    $result = db_query("SELECT sid, nid FROM webform_submissions INNER JOIN node USING(nid) WHERE nid IN(:nids) AND submitted BETWEEN :start AND :end-1 ORDER BY sid", [':nids' => $nids, ':start' => $start, ':end' => $end]);
    foreach ($result as $row) {
      yield Submission::load($row->nid, $row->sid);
      drupal_static_reset('webform_get_submission');
    }
  }

  protected function getNodes() {
    list($start, $end) = $this->timeframe->getTimeStamps();
    $nids = db_query("SELECT nid FROM webform_submissions INNER JOIN node USING(nid) WHERE type IN(:types) AND submitted BETWEEN :start AND :end-1 GROUP BY nid ORDER BY nid", [':types' => $this->types, ':start' => $start, ':end' => $end])
      ->fetchCol();
    return entity_load('node', $nids);
  }

  protected function submissionInformationData(Submission $submission, array $options, $row_count) {
    $data = module_invoke_all('webform_results_download_submission_information_data', $submission, $options, 0, $row_count);
    $context = array('submission' => $submission, 'options' => $options, 'serial_start' => 0, 'row_count' => $row_count);
    drupal_alter('webform_results_download_submission_information_data', $data, $context);
    return $data;
  }

  protected function getDownloadOptions() {
    $options = [];
    foreach ($this->nodes as $node) {
      $options[$node->nid] = [
        'select_format' => 'compact',
        'select_keys' => TRUE,
      ] + webform_results_download_default_options($node, 'delimited');
    }
    return $options;
  }

  public function writeTo(CsvWriter $writer) {
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
          $slots[$slot_id]['headers'][] = $component_exporter->csvHeaders();
          $slots[$slot_id]['components'][$node->nid] = $component_exporter;
        }
      }
    }

    $slot_headers = [];
    foreach ($slots as $slot_id => $slot) {
      $slot_header = [[], [], []];
      foreach ($slot['headers'] as $component_headers) {
        foreach ($component_headers as $row_nr => $row) {
          if (!is_array($row)) {
            $row = [$row];
          }
          foreach ($row as $col_nr => $col_label) {
            if (!isset($slot_header[$row_nr][$col_nr]) || !in_array($col_label, $slot_header[$row_nr][$col_nr])) {
              $slot_header[$row_nr][$col_nr][] = $col_label;
            }
          }
        }
      }
      $slot_headers[$slot_id] = $slot_header;
    }

    foreach ($slot_headers as $slot_id => $header) {
      $header[] = [[$slot_id]];
      $slot_headers[$slot_id] = $this->normalizeRowLength($header);
    }

    for ($row_num = 0; $row_num <= 3; $row_num++) {

      if ($row_num == 2) {
        $row = array_map(function ($x) { return $x['title']; }, $submission_info_cols);
      }
      else {
        $row = array_fill(0, count($submission_info_cols), '');
      }

      foreach ($slot_headers as $headers) {
        foreach ($headers[$row_num] as $cell_candidates) {
          $row[] = implode(' / ', $cell_candidates);
        }
      }
      $writer->writeRow($row);
    }

    $row_count = 0;
    foreach ($this->getSubmissions() as $submission) {
      $nid = $submission->nid;
      $options = $options_by_nid[$nid];
      $row = [];

      // Add submission information.
      $data = $this->submissionInformationData($submission, $options, $row_count);
      foreach (array_keys($submission_info_cols) as $token) {
        $row[] = isset($data[$token]) ? $data[$token] : '';
      }

      // Add submission data.
      foreach ($slots as $slot_id => $slot) {
        if (isset($slot['components'][$nid])) {
          $component_exporter = $slot['components'][$nid];
          $row = array_merge($row, $component_exporter->csvRow($submission));
        }
        else {
          $row[] = '';
        }
      }
      $writer->writeRow($row);
      $row_count++;
    }
  }

}

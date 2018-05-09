<?php

namespace Drupal\campaignion_csv\Exporter\WebformGeneric;

use Drupal\little_helpers\Webform\Submission;

/**
 * One or more columns filled-in by one or more components.
 */
class Slot {

  protected $id;
  protected $components;

  public function __construct($slot_id) {
    $this->slot_id = $id;
  }

  public function setComponent($nid, ComponentExporter $component_exporter) {
    $this->components[$nid] = $component_exporter;
  }

  public function headers() {
    $header = [[], [], []];
    foreach ($this->components as $component) {
      foreach ($component->csvHeaders() as $row_nr => $row) {
        if (!is_array($row)) {
          $row = [$row];
        }
        foreach ($row as $col_nr => $col_label) {
          if (!isset($header[$row_nr][$col_nr]) || !in_array($col_label, $header[$row_nr][$col_nr])) {
            $header[$row_nr][$col_nr][] = $col_label;
          }
        }
      }
    }
    $header[] = [[$this->slot_id]];
    $header = $this->normalizeRowLength($header);
    foreach ($header as $row_num => $row) {
      foreach ($row as $col_num => $cell_candidates) {
        $header[$row_num][$col_num] = implode(' / ', $cell_candidates);
      }
    }
    return $header;
  }

  protected function normalizeRowLength($rows, $fill = []) {
    $count = function($x) {
      return count($x);
    };
    $max_cols = max(array_map($count, $rows));

    $fill_row = function($x) use ($max_cols, $fill) {
      $c = count($x);
      return array_merge($x, array_fill(0, $max_cols - $c, $fill));
    };
    return array_map($fill_row, $rows);
  }

  public function row(Submission $submission) {
    $nid = $submission->nid;
    if (isset($this->components[$nid])) {
      return $this->components[$nid]->csvRow($submission);
    }
    else {
      // @TODO: This should conform to the slot width.
      return [''];
    }
  }

}

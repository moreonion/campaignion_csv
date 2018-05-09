<?php

namespace Drupal\campaignion_csv\Exporter\WebformGeneric;

use Drupal\little_helpers\Webform\Submission;

/**
 * One or more columns in the CSV table filled-in by one or more components.
 */
class Slot {

  protected $id;
  protected $components;

  /**
   * Create new slot.
   *
   * @param string $slot_id
   *   The unique ID for this slot.
   */
  public function __construct($slot_id) {
    $this->slot_id = $id;
  }

  /**
   * Set the component that has this slot for a given node.
   *
   * @param int $nid
   *   The `$node->nid` of the component.
   * @param \Drupal\campaignion_csv\Exporter\WebformGeneric\ComponentExporter $component_exporter
   *   The exporter for the component.
   */
  public function setComponent($nid, ComponentExporter $component_exporter) {
    $this->components[$nid] = $component_exporter;
  }

  /**
   * Generate the header rows for this slot.
   *
   * @return string[][]
   *   4 header rows containing a consistent number of cells.
   */
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

  /**
   * Fill all of sub-arrays so that they have the same number of items.
   *
   * @param array[] $rows
   *   The arrays to fill.
   * @param mixed $fill
   *   The fill value.
   *
   * @return array[]
   *   The input-array with all sub-arrays filled to the maximum sub-array
   *   length.
   */
  protected function normalizeRowLength(array $rows, $fill = []) {
    $count = function ($x) {
      return count($x);
    };
    $max_cols = max(array_map($count, $rows));

    $fill_row = function ($x) use ($max_cols, $fill) {
      $c = count($x);
      return array_merge($x, array_fill(0, $max_cols - $c, $fill));
    };
    return array_map($fill_row, $rows);
  }

  /**
   * Generate the CSV row for a submission.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The submission that’s being exported.
   *
   * @return string[]
   *   The cells for this slot and submission.
   */
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

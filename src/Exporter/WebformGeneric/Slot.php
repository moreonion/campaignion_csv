<?php

namespace Drupal\campaignion_csv\Exporter\WebformGeneric;

use Drupal\little_helpers\Webform\Submission;

/**
 * One or more columns in the CSV table filled-in by one or more components.
 */
class Slot {

  protected $id;
  protected $components;
  protected $rowLength;

  /**
   * Create new slot.
   *
   * @param string $slot_id
   *   The unique ID for this slot.
   */
  public function __construct($slot_id) {
    $this->slot_id = $slot_id;
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
    $header = $this->normalizeHeaders($header);
    $this->rowLength = count($header[0]);
    foreach ($header as $row_num => $row) {
      foreach ($row as $col_num => $cell_candidates) {
        $header[$row_num][$col_num] = implode(' / ', $cell_candidates);
      }
    }
    return $header;
  }

  /**
   * Normalize length of the header rows.
   *
   * @param array[] $rows
   *   The arrays to fill.
   *
   * @return array[]
   *   The input-array with all sub-arrays filled to the maximum sub-array
   *   length.
   */
  protected function normalizeHeaders(array $rows) {
    $count = function ($x) {
      return count($x);
    };
    $this->rowLength = max(array_map($count, $rows));
    return array_map([$this, 'cutAndFillRow'], $rows);
  }

  /**
   * Fill or cut a row to the standard length.
   *
   * @param array $row
   *   Array to fill.
   * @param mixed $fill
   *   The value used to append.
   *
   * @return array
   *   The array filled up or cut to $this->rowLength items.
   */
  protected function cutAndFillRow(array $row, $fill = []) {
    $n = $this->rowLength - count($row);
    if ($n == 0) {
      return $row;
    }
    if ($n > 0) {
      return array_merge($row, array_fill(0, $n, $fill));
    }
    return array_slice($row, 0, $this->rowLength);
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
    $row = ($c = $this->components[$nid] ?? NULL) ? $c->csvRow($submission) : [];
    return $this->cutAndFillRow($row, '');
  }

}

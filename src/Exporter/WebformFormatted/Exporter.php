<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

use Drupal\campaignion_csv\Timeframe;
use Drupal\campaignion_csv\Files\CsvFile;
use Drupal\little_helpers\Webform\Submission;

/**
 * Exporter for exporting webform submissions into a fixed format.
 *
 * The columns for this format are described using Column objects.
 */
class Exporter {

  /**
   * Get all nodes with submissions in the specified timeframe.
   *
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   Get nodes with submissions in this timeframe.
   * @param array $criteria
   *   Additional criteria for the node query. This has to be something that
   *   makes sense in a query with `node n` and `webform_submissions s` tables.
   *
   * @return object[]
   *   Nodes keyed by their nids.
   */
  protected static function getNodes(Timeframe $timeframe, array $criteria = []) {
    list($start, $end) = $timeframe->getTimeStamps();
    $q = db_select('webform_submissions', 's');
    $q->innerJoin('node', 'n', 'n.nid = s.nid');
    $q->fields('n', ['nid'])
      ->condition('submitted', [$start, $end - 1], 'BETWEEN');
    foreach ($criteria as $column => $values) {
      $q->condition($column, $values);
    }
    $nids = $q->groupBy('nid')
      ->execute()
      ->fetchCol();
    return entity_load('node', $nids);
  }

  /**
   * Create new instance from an info-array.
   *
   * @param array $info
   *   Info from campaignion_csv_info() or added by the FilePattern.
   */
  public static function fromInfo(array $info) {
    $info += [
      'criteria' => [],
      'colmuns' => [],
    ];
    if (!isset($info['nodes'])) {
      $info['nodes'] = static::getNodes($info['timeframe'], $info['criteria']);
    }
    $columns = [];
    foreach ($info['columns'] as $label => $column_info) {
      $column_info['label'] = $label;
      $columns[] = Column::fromInfo($column_info);
    }
    return new static($info['timeframe'], $info['nodes'], $columns);
  }

  /**
   * Create new instance.
   *
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   Export submissions within this timeframe.
   * @param object[] $nodes
   *   Export submissions from these nodes.
   * @param \Drupal\campaignion_csv\Exporter\WebformFormatted\Column[] $columns
   *   Columns that define the export format.
   */
  public function __construct(Timeframe $timeframe, array $nodes, array $columns) {
    $this->timeframe = $timeframe;
    $this->nodes = $nodes;
    $this->columns = $columns;
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
      $submission = Submission::load($row->nid, $row->sid);
      // Skip empty submissions and submissions without email address.
      if (!$submission->data || !$submission->valueByKey('email')) {
        continue;
      }
      yield $submission;
      drupal_static_reset('webform_get_submission');
    }
  }

  /**
   * Write submission files to the CsvFile.
   */
  public function writeTo(CsvFile $file) {
    $row = array_map(function ($v) {
      return $v->label;
    }, $this->columns);
    $file->writeRow($row);

    foreach ($this->getSubmissions() as $submission) {
      $row = [];
      foreach ($this->columns as $column) {
        $row[] = $column->value($submission);
      }
      $file->writeRow($row);
    }
  }

}

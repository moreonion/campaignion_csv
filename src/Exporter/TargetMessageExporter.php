<?php

namespace Drupal\campaignion_csv\Exporter;

use Drupal\campaignion_csv\Exporter\WebformFormatted\Column;
use Drupal\campaignion_csv\Files\CsvFileInterface;
use Drupal\campaignion_csv\Timeframe;
use Drupal\little_helpers\Webform\Submission;

/**
 * Export messages sent to targets.
 */
class TargetMessageExporter {

  /**
   * The timeframe for the current export.
   *
   * @var \Drupal\campaignion_csv\Timeframe
   */
  protected $timeframe;

  /**
   * Mapping of column headers to data paths.
   *
   * @var string[]
   */
  protected $mapping;

  /**
   * Submission columns to export in addition to the target / message data.
   *
   * @var \Drupal\campaignion_csv\Exporter\WebformFormatted\Column[] $columns
   */
  protected $columns;

  /**
   * Create a new exporter based on the info.
   */
  public static function fromInfo(array $info) {
    $info += [
      'columns' => [],
      'mapping' => [
        'cid' => 'cid',
        'Salutation' => 'target.salutation',
        'Area / Constituency' => 'target.area.name',
        'Area type' => 'target.area.type',
        'Area code' => 'target.area.gss_code',
        'Country' => 'target.area.country__name',
        'Display name' => 'message.display',
        'To-name' => 'message.toName',
        'Subject' => 'message.subject',
        'Header' => 'message.header',
        'Message' => 'message.message',
        'Footer' => 'message.footer',
      ],
    ];
    foreach ($info['columns'] as $label => $column_info) {
      $column_info['label'] = $label;
      $columns[] = Column::fromInfo($column_info);
    }
    return new static($info['timeframe'], $columns, $info['mapping']);
  }

  /**
   * Create a new instance.
   *
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   Export data for submissions within this timeframe.
   * @param \Drupal\campaignion_csv\Exporter\WebformFormatted\Column[] $columns
   *   Submission columns to export in addition to the target / message data.
   * @param string[] $mapping
   *   Mapping of column headers to data paths in the e2t_selector data.
   */
  public function __construct(Timeframe $timeframe, array $columns, array $mapping) {
    $this->timeframe = $timeframe;
    $this->columns = $columns;
    $this->mapping = $mapping;
  }

  /**
   * Iterate through submitted data of e2t_selector components.
   */
  protected function readSubmittedData() {
    $last_sid = 0;
    list($start, $end) = $this->timeframe->getTimestamps();
    $sql_sids = <<<SQL
SELECT s.nid, s.sid, c.cid
FROM webform_submissions s
  INNER JOIN webform_component c USING(nid)
WHERE s.is_draft=0 AND c.type='e2t_selector' AND s.submitted BETWEEN :start AND :end AND s.sid>:last_sid
GROUP BY s.sid
ORDER BY s.sid
LIMIT 100
SQL;
    $args = [':start' => $start, ':end' => $end - 1];
    while ($rows = db_query($sql_sids, [':last_sid' => $last_sid] + $args)->fetchAll()) {
      foreach ($rows as $row) {
        $submission = Submission::load($row->nid, $row->sid);
        $values = array_map('unserialize', $submission->valuesByCid($row->cid));
        $values['cid'] = $row->cid;
        foreach ($values as $value) {
          yield [$submission, $value];
        }
        $last_sid = $row->sid;
      }
      drupal_static_reset('webform_get_submission');
    }
  }

  /**
   * Write the data to the CsvFile.
   */
  public function writeTo(CsvFileInterface $file) {
    $header = array_map(function ($v) {
      return $v->label;
    }, $this->columns);
    $file->writeRow(array_merge($header, array_keys($this->mapping)));

    foreach ($this->readSubmittedData() as $pair) {
      list($submission, $value) = $pair;
      $row = [];
      foreach ($this->columns as $column) {
        $row[] = $column->value($submission);
      }
      $value_row = array_map(function ($path) use ($value) {
        return drupal_array_get_nested_value($value, explode('.', $path)) ?? '';
      }, $this->mapping);
      $file->writeRow(array_merge($row, $value_row));
    }
  }

}

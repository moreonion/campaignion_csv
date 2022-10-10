<?php

namespace Drupal\campaignion_csv\Exporter;

use Drupal\campaignion_csv\Files\CsvFileInterface;
use Drupal\campaignion_csv\Timeframe;

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
   * Create a new exporter based on the info.
   */
  public static function fromInfo(array $info) {
    $info += [
      'mapping' => [
        'nid' => 'nid',
        'sid' => 'sid',
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
    return new static($info['timeframe'], $info['mapping']);
  }

  /**
   * Create a new instance.
   *
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   Export data for submissions within this timeframe.
   * @param string[] $mapping
   *   Mapping of column headers to data paths.
   */
  public function __construct(Timeframe $timeframe, array $mapping) {
    $this->timeframe = $timeframe;
    $this->mapping = $mapping;
  }

  /**
   * Iterate through submitted data of e2t_selector components.
   */
  protected function readSubmittedData() {
    $last_sid = 0;
    list($start, $end) = $this->timeframe->getTimestamps();
    $sql_sids = <<<SQL
SELECT s.sid
FROM webform_submissions s
  INNER JOIN webform_component c USING(nid)
WHERE s.is_draft=0 AND c.type='e2t_selector' AND s.submitted BETWEEN :start AND :end AND s.sid>:last_sid
GROUP BY s.sid
ORDER BY s.sid
LIMIT 100
SQL;
    $args = [':start' => $start, ':end' => $end - 1];
    while ($sids = db_query($sql_sids, [':last_sid' => $last_sid] + $args)->fetchCol()) {
      $sql = <<<SQL
SELECT s.sid, s.nid, c.cid, d.data
FROM webform_submissions s
  INNER JOIN webform_component c USING(nid)
  INNER JOIN webform_submitted_data d USING(nid, sid, cid)
WHERE s.sid IN (:sids) AND c.type='e2t_selector'
ORDER BY s.sid, c.cid, d.no
SQL;
      $rows = db_query($sql, [':sids' => $sids])->fetchAll();
      foreach ($rows as $row) {
        $data = unserialize($row->data);
        $row->target = $data['target'];
        $row->message = $data['message'];
        yield (array) $row;
        $last_sid = $row->sid;
      }
      $last_sid = end($sids);
    }
  }

  /**
   * Write the data to the CsvFile.
   */
  public function writeTo(CsvFileInterface $file) {
    $file->writeRow(array_keys($this->mapping));
    foreach ($this->readSubmittedData() as $row) {
      $row = array_map(function ($path) use ($row) {
          return drupal_array_get_nested_value($row, explode('.', $path)) ?? '';
      }, $this->mapping);
      $file->writeRow($row);
    }
  }

}

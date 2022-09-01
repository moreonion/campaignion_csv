<?php

namespace Drupal\campaignion_csv\Exporter;

use Drupal\campaignion_csv\Files\CsvFileInterface;
use Drupal\campaignion_csv\Timeframe;

/**
 * Export per-node message stats for email-to-target targets.
 */
class TargetStatsExporter {

  /**
   * The timeframe for the current export.
   *
   * @var \Drupal\campaignion_csv\Timeframe
   */
  protected $timeframe;

  /**
   * Create a new exporter based on the info.
   */
  public static function fromInfo(array $info) {
    return new static($info['nid'], $info['timeframe']);
  }

  /**
   * Create a new instance.
   *
   * @param int $nid
   *   The node to export target stats for.
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   Export data for submissions within this timeframe.
   */
  public function __construct(int $nid, Timeframe $timeframe) {
    $this->timeframe = $timeframe;
    $this->nid = $nid;
  }

  /**
   * Create a sorted list of targets and message counts.
   */
  protected function readTargetData() {
    list($start, $end) = $this->timeframe->getTimestamps();
    $sql = <<<SQL
SELECT s.sid, d.data
FROM webform_submissions s
  INNER JOIN webform_component c USING(nid)
  INNER JOIN webform_submitted_data d USING(nid, sid, cid)
WHERE s.nid=:nid AND s.is_draft=0 AND c.type='e2t_selector' AND s.submitted BETWEEN :start AND :end AND s.sid>:last_sid
LIMIT 10000
SQL;
    $last_sid = 0;
    $args = [':start' => $start, ':end' => $end - 1, ':nid' => $this->nid];
    $targets = [];
    while ($rows = db_query($sql, $args + [':last_sid' => $last_sid])->fetchAll()) {
      foreach ($rows as $row) {
        $data = unserialize($row->data);
        $target = $data['target'];
        if (!isset($targets[$target['id']])) {
          $targets[$target['id']]['# messages'] = 0;
        }
        $targets[$target['id']] += array_filter([
          'ID' => $target['id'],
          'Display name' => $data['message']['display'],
          'Salutation' => $target['salutation'],
          'Party' => $target['party'] ?? $target['political_affiliation'] ?? '',
          'Area / Constituency' => $target['area']['name'] ?? '',
          'Area type' => $target['area']['type'] ?? '',
          'Area code' => $target['area']['gss_code'] ?? '',
          'Country' => $target['area']['country__name'] ?? $target['constituency']['country']['name'] ?? '',
        ]);
        $targets[$target['id']]['# messages'] += 1;
        $last_sid = $row->sid;
      }
    }
    usort($targets, function ($a, $b) {
      // Exchanging b and a here reverses the sort order: big values first.
      return $b['# messages'] <=> $a['# messages'];
    });
    return $targets;
  }

  /**
   * Write the data to the CsvFile.
   */
  public function writeTo(CsvFileInterface $file) {
    $header = [
      'ID',
      'Display name',
      'Salutation',
      'Party',
      'Area / Constituency',
      'Area type',
      'Area code',
      'Country',
      '# messages',
    ];
    $file->writeRow($header);

    foreach ($this->readTargetData() as $target) {
      $row = array_map(function ($key) use ($target) {
        return $target[$key] ?? '';
      }, $header);
      $file->writeRow($row);
    }
  }

}

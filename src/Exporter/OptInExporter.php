<?php

namespace Drupal\campaignion_csv\Exporter;

use Drupal\campaignion_csv\Files\CsvFile;
use Drupal\campaignion_csv\Timeframe;

/**
 * Export all redhen contacts.
 */
class OptInExporter {

  protected $timeframe;
  protected $dateFormat;

  /**
   * @var bool
   *
   * Opt-ins are exported if this flag is TRUE and opt-outs if it’s FALSE.
   */
  protected $optIn;

  /**
   * Create a new exporter based on the info.
   */
  public static function fromInfo(array $info) {
    $info += [
      'date_format' => 'Y-m-d H:i:s',
      'opt_in' => TRUE,
    ];
    return new static($info['timeframe'], $info['date_format'], $info['opt_in']);
  }

  /**
   * Create a new instance.
   *
   * @param \Drupal\campaignion_csv\Timeframe $timeframe
   *   Export activities within this timeframe.
   * @param string $date_format
   *   Date format used when exporting.
   */
  public function __construct(Timeframe $timeframe, $date_format, $opt_in) {
    $this->timeframe = $timeframe;
    $this->dateFormat = $date_format;
    $this->optIn = $opt_in;
  }

  /**
   * Build a query to get all activity data for the timeframe.
   *
   * @return \SelectQueryInterface
   *   The query.
   */
  protected function buildQuery() {
    list($start, $end) = $this->timeframe->getTimestamps();
    $q = db_select('campaignion_activity', 'ca')
      ->fields('ca', ['contact_id', 'created']);
    $q->leftJoin('campaignion_opt_in', 'o', 'o.activity_id=ca.activity_id');
    $q->fields('o', ['id', 'channel', 'statement']);
    $q->condition('ca.created', [$start, $end - 1], 'BETWEEN');
    $q->condition('o.operation', $this->optin ? 1 : 0);
    $q->orderBy('o.id');
    return $q;
  }

  /**
   * Write the data to the CsvFile.
   */
  public function writeTo(CsvFile $file) {
    $header = [
      '#',
      'Time',
      'Contact ID',
      'Channel',
      'Statement',
    ];
    $file->writeRow($header);

    foreach ($this->buildQuery()->execute() as $r) {
      $row = [
        $r->id,
        format_date($r->created, 'custom', $this->dateFormat),
        $r->contact_id,
        $r->channel,
        $r->statement,
      ];
      $file->writeRow($row);
    }
  }

}

<?php

namespace Drupal\campaignion_csv;

use Drupal\campaignion\ContactTypeManager;

/**
 * Export all redhen contacts.
 */
class ActivityExporter {

  public function fromInfo(Timeframe $timeframe, array $info) {
    $info += [
      'date_format' => 'Y-m-d H:i:s',
    ];
    return new static($timeframe, $info['date_format']);
  }

  public function __construct(Timeframe $timeframe, $date_format) {
    $this->timeframe = $timeframe;
    $this->dateFormat = $date_format;
  }

  protected function buildQuery() {
    list($start, $end) = $this->timeframe->getTimestamps();
    $q = db_select('campaignion_activity', 'ca')
      ->fields('ca', ['activity_id', 'contact_id', 'type', 'created']);
    $q->leftJoin('campaignion_activity_webform', 'caw', 'ca.activity_id=caw.activity_id');
    $q->fields('caw', ['nid', 'sid', 'confirmed']);
    $q->leftJoin('campaignion_activity_payment', 'cap', 'ca.activity_id=cap.activity_id');
    $q->fields('cap', ['pid']);
    $q->leftJoin('campaignion_activity_newsletter_subscription', 'cans', 'ca.activity_id=cans.activity_id');
    $q->fields('cans', ['action', 'from_provider', 'optin_statement']);
    $q->condition('ca.created', [$start, $end - 1], 'BETWEEN');
    $q->orderBy('ca.activity_id');
    return $q;
  }

  public function writeTo(CsvWriter $writer) {
    $header = [
      'Activity ID',
      'Contact ID',
      'Type',
      'Time',
      'nid',
      'sid',
      'Confirmation Time',
      'pid',
      'List ID',
      'List action',
      'from provider',
      'Opt-In statement',
    ];
    $writer->writeRow($header);

    foreach ($this->buildQuery()->execute() as $r) {
      $row = [
        $r->activity_id,
        $r->contact_id,
        $r->type,
        format_date($r->created, 'custom', $this->dateFormat),
        $r->nid,
        $r->sid,
        $r->confirmed ? format_date($r->confirmed, 'custom', $this->dateFormat) : '',
        $r->pid,
        $r->list_id,
        $r->action,
        $r->from_provider,
        $r->optin_statement,
      ];
      $writer->writeRow($row);
    }
  }

}

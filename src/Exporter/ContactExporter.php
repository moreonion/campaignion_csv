<?php

namespace Drupal\campaignion_csv\Exporter;

use Drupal\campaignion\ContactTypeManager;
use Drupal\campaignion_csv\Files\CsvFileInterface;

/**
 * Export all redhen contacts of a bundle.
 *
 * The export format is based on the `csv` exporter defined for the bundle.
 */
class ContactExporter {

  protected $bundle;
  protected $range;

  /**
   * Create a new exporter based on the info array.
   *
   * @param array $info
   *   Info-array as specified in hook_campaignion_csv_info().
   */
  public static function fromInfo(array $info) {
    $info += [
      'bundle' => 'contact',
      'exporter' => 'csv',
      'header_rows' => 2,
    ];
    return new static($info['bundle'], $info['range'], $info['exporter'], $info['header_rows']);
  }

  /**
   * Create a new instance.
   *
   * @param string $bundle
   *   The redhen_contact bundle that should be exported.
   * @param int[] $range
   *   Contact ID range for this export.
   * @param string $exporter
   *   The exporter format to use for this contact export.
   * @param int $header_rows
   *   Number of header lines that should be produced.
   */
  public function __construct($bundle, array $range, $exporter = 'csv', $header_rows = 2) {
    $this->bundle = $bundle;
    $this->range = $range;
    $this->exporter = $exporter;
    $this->headerRows = $header_rows;
  }

  /**
   * Generator: Iterate over all contacts of the bundle.
   */
  protected function contacts() {
    $last_id = 0;
    $count = 0;
    list($min_id, $max_id) = $this->range;
    while (TRUE) {
      $contact_ids = db_select('redhen_contact', 'c')
        ->fields('c', ['contact_id'])
        ->condition('type', $this->bundle)
        ->condition('contact_id', $last_id, '>')
        ->condition('contact_id', $min_id, '>=')
        ->condition('contact_id', $max_id, '<')
        ->orderBy('contact_id')
        ->range(0, 100)
        ->execute()
        ->fetchCol();
      if (!$contact_ids) {
        break;
      }

      $contacts = entity_load('redhen_contact', $contact_ids, [], TRUE);
      foreach ($contacts as $contact) {
        yield $contact;
        $last_id = $contact->contact_id;
      }
    }
  }

  /**
   * Write exported contacts to a CsvFile.
   */
  public function writeTo(CsvFileInterface $file) {
    $exporter = ContactTypeManager::instance()
      ->exporter($this->exporter, $this->bundle);
    for ($row = 0; $row < $this->headerRows; $row++) {
      $file->writeRow($exporter->header($row));
    }
    foreach ($this->contacts() as $contact) {
      $exporter->setContact($contact);
      $file->writeRow($exporter->row());
    }
  }

}

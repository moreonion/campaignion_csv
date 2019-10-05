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
    return new static($info['bundle'], $info['range']);
  }

  /**
   * Create a new instance.
   *
   * @param string $bundle
   *   The redhen_contact bundle that should be exported.
   * @param int[] $range
   *   Contact ID range for this export.
   */
  public function __construct($bundle, array $range) {
    $this->bundle = $bundle;
    $this->range = $range;
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
      ->exporter('csv', $this->bundle);
    $file->writeRow($exporter->header(0));
    $file->writeRow($exporter->header(1));
    foreach ($this->contacts() as $contact) {
      $exporter->setContact($contact);
      $file->writeRow($exporter->row());
    }
  }

}

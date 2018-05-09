<?php

namespace Drupal\campaignion_csv\Exporter;

use Drupal\campaignion\ContactTypeManager;
use Drupal\campaignion_csv\Files\CsvFile;

/**
 * Export all redhen contacts.
 */
class ContactExporter {

  public function fromInfo(array $info) {
    return new static($info['bundle']);
  }

  public function __construct($bundle) {
    $this->bundle = $bundle;
  }

  protected function contacts() {
    $last_id = 0;
    while (TRUE) {
      $contact_ids = db_select('redhen_contact', 'c')
        ->fields('c', ['contact_id'])
        ->condition('type', $this->bundle)
        ->condition('contact_id', $last_id, '>')
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

  public function writeTo(CsvFile $file) {
    $exporter = ContactTypeManager::instance()
      ->exporter('csv', $this->bundle);
    $file->writeRow($exporter->header(0));
    $file->writeRow($exporter->header(1));
    foreach ($this->contacts() as $contact) {
      $row = [];
      $exporter->setContact($contact);
      $file->writeRow($exporter->row());
    }
  }

}

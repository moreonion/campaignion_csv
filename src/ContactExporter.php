<?php

namespace Drupal\campaignion_csv;

use Drupal\campaignion\ContactTypeManager;

/**
 * Export all redhen contacts.
 */
class ContactExporter {

  public function fromInfo(Timeframe $timeframe, array $info) {
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

  public function writeTo(CsvWriter $writer) {
    $exporter = ContactTypeManager::instance()
      ->exporter('csv', $this->bundle);
    $writer->writeRow($exporter->header(0));
    $writer->writeRow($exporter->header(1));
    foreach ($this->contacts() as $contact) {
      $row = [];
      $exporter->setContact($contact);
      $writer->writeRow($exporter->row());
    }
  }

}

<?php

namespace Drupal\campaignion_csv\Exporter;

use Upal\DrupalUnitTestCase;

use Drupal\campaignion\Contact;
use Drupal\campaignion_csv\Files\CsvFile;

/**
 * Test the contact exporter.
 */
class ContactExporterTest extends DrupalUnitTestCase {

  /**
   * Prepare webform with an email and textfield component plus one submission.
   */
  public function setUp(): void {
    parent::setUp();
    $this->path = tempnam(sys_get_temp_dir(), 'contact-export-test');
    $this->contact = Contact::fromEmail('test@example.com');
    $this->contact->save();
  }

  /**
   * Delete the webform node.
   */
  public function tearDown(): void {
    $this->contact->delete();
    unlink($this->path);
  }

  /**
   * Test exporting a single test contact.
   */
  public function testExport() {
    $contact_id = $this->contact->contact_id;
    $info = [
      'range' => [$contact_id, $contact_id + 1],
    ];
    $exporter = ContactExporter::fromInfo($info);
    $file = new CsvFile($this->path, 'w');
    $exporter->writeTo($file);

    $read_file = new CsvFile($this->path, 'r');
    $rows = [];
    while ($row = $read_file->fgetcsv()) {
      $rows[] = $row;
    }
    $this->assertEqual(['Email'], $rows[0]);
    $this->assertEqual(['test@example.com'], $rows[2]);
  }

}

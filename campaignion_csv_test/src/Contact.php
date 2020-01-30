<?php

namespace Drupal\campaignion_csv_test;

use Drupal\campaignion\ContactTypeInterface;
use Drupal\campaignion\CRM\CsvExporter;
use Drupal\campaignion\CRM\ImporterBase;
use Drupal\campaignion\CRM\Export\Label;
use Drupal\campaignion\CRM\Export\WrapperField;

/**
 * Contact-type for the "contact" contact-type.
 */
class Contact implements ContactTypeInterface {

  /**
   * Get a new contact-type instance.
   */
  public function __construct() {
  }

  /**
   * Create an importer for this contact-type.
   *
   * @param string $type
   *   The type of the importer. Usually importers are named by their source.
   */
  public function importer($type) {
    return new ImporterBase([]);
  }

  /**
   * Create an exporter for this contact-type.
   *
   * @param string $type
   *   The type of the exporter. Usually exporters are named by their target.
   * @param string $language
   *   Language used for translated values.
   */
  public function exporter($type, $language) {
    $map = array();
    switch ($type) {
      case 'csv':
        $map['redhen_contact_email'] = new Label(t('Email'), new WrapperField('email'));
        return new CsvExporter($map);
    }
  }

}

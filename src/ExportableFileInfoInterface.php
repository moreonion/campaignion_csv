<?php

namespace Drupal\campaignion_csv;

/**
 * Represents a single exportable file in then managed directory.
 */
interface ExportableFileInfoInterface {

  /**
   * Create or update the file if needed.
   *
   * It’s guaranteed setExporterFactory() will be called before update().
   */
  public function update();

  /**
   * Set the exporter factory for this file.
   *
   * @param object $factory
   *   The exporter factory.
   */
  public function setExporterFactory($factory);

}

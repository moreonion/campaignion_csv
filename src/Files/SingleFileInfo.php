<?php

namespace Drupal\campaignion_csv\Files;

/**
 * A single file that’s updated regularly.
 */
class SingleFileInfo extends CsvFileInfo {

  protected $refreshInterval;

  /**
   * Create a new instance.
   *
   * @param string $path
   *   Full path to the file (even if it does not yet exist).
   * @param \DateInterval $refresh_interval
   *   The minimum interval that needs to pass between two builds of the file.
   */
  public function __construct($path, \DateInterval $refresh_interval) {
    parent::__construct($path);
    $this->refreshInterval = $refresh_interval;
  }

  /**
   * Checks whether the file needs to be rebuilt.
   *
   * The file is built whenever:
   * - It doesn’t exist yet.
   * - The refresh interval has passed since the last build.
   *
   * @return bool
   *   TRUE if the file should be rebuilt otherwise FALSE.
   */
  protected function needsBuild() {
    if (!$this->isFile()) {
      return TRUE;
    }
    $t = new \DateTime();
    $t->sub($this->refreshInterval);
    return $t->getTimestamp() > $this->getMTime();
  }

}

<?php

namespace Drupal\campaignion_csv\Files;

/**
 * File pattern usable in the campaignion_csv_info() definitions.
 */
interface FilePatternInterface {

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info-array as defined in hook_campaignion_csv_info().
   */
  public static function fromInfo(array $info, \DateTimeInterface $now = NULL);

  /**
   * Expand the file pattern and create the specfic files.
   *
   * @param string $root
   *   The path to the root-directory. The pattern is interpreted relative to
   *   the root-directory.
   *
   * @return \Drupal\campaignion_csv\ExportableFileInfoInterface[]
   *   Array of file info objects keyed by their expanded path.
   */
  public function expand($root);

}

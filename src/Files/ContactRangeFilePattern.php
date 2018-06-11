<?php

namespace Drupal\campaignion_csv\Files;

/**
 * File pattern that creates one file every X contacts.
 */
class ContactRangeFilePattern implements FilePatternInterface {

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info-array as defined in hook_campaignion_csv_info(). Keys are:
   *   - path: The path pattern for the file relative to the root. The path
   *     is expanded using `strftime()`.
   *   - refresh_interval: A \DateInterval that defines how often files for the
   *     ongoing period are regenerated.
   *   - bundle: Machine name of the redhen_contact type.
   *   - contacts_per_file: Number of contacts per file.
   * @param \DateTimeInterface $now
   *   The time considered to be now. Defaults to the date and time.
   */
  public static function fromInfo(array $info, \DateTimeInterface $now = NULL) {
    $info += [
      'bundle' => 'contact',
      'contacts_per_file' => 100000,
    ];
    return new static($info['path'], $info['bundle'], $info['contacts_per_file'], $info['refresh_interval']);
  }

  /**
   * Create a new monthly file pattern.
   *
   * @param string $path
   *   The path pattern in `strftime()`-format.
   * @param string $bundle
   *   The redhen contact type that’s being exported.
   * @param int $contacts_per_file
   *   Number of contacts exported to a single file (at maximum).
   * @param \DateInterval $refresh_interval
   *   The minimum interval that needs to pass between two builds of the file.
   */
  public function __construct($path, $bundle, $contacts_per_file, \DateInterval $refresh_interval) {
    $this->pathPattern = $path;
    $this->bundle = $bundle;
    $this->contactsPerFile = $contacts_per_file;
    $this->refreshInterval = $refresh_interval;
  }

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
  public function expand($root) {
    $files = [];
    $max_id = db_select('redhen_contact', 'c')
      ->fields('c', ['contact_id'])
      ->condition('c.type', $this->bundle)
      ->orderBy('contact_id', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchField();

    $range_start = 0;
    $i = 0;
    while ($range_start <= $max_id) {
      $path = sprintf($this->pathPattern, $i++);
      $range_end = $range_start + $this->contactsPerFile;
      $files[$path] = new ContactRangeFileInfo($root . '/' . $path, $this->bundle, [$range_start, $range_end], $this->refreshInterval);
      $range_start = $range_end;
    }
    return $files;
  }

}

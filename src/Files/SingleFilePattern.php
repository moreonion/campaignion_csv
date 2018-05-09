<?php

namespace Drupal\campaignion_csv\Files;

/**
 * File pattern that creates a single file that’s updated regularly.
 */
class SingleFilePattern implements FilePatternInterface {

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info-array as defined in hook_campaignion_csv_info(). Keys are:
   *   - path: The path pattern for the file relative to the root. The path
   *     is expanded using `strftime()`.
   *   - refresh_interval: A \DateInterval that defines how often the file
   *     updated.
   */
  public static function fromInfo(array $info, \DateTimeInterface $now = NULL) {
    $info += [
      'refresh_interval' => new \DateInterval('PT23H30M'),
    ];
    return new static($info['path'], $info['refresh_interval']);
  }

  /**
   * Create a new monthly file pattern.
   *
   * @param string $path
   *   The path pattern in `strftime()`-format.
   * @param \DateInterval $refresh_interval
   *   The minimum interval that needs to pass between two builds of the file.
   */
  public function __construct($path, \DateInterval $refresh_interval) {
    $this->path = $path;
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
    $files[$this->path] = new SingleFileInfo($root . '/' . $this->path, $this->refreshInterval);
    return $files;
  }

}

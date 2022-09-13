<?php

namespace Drupal\campaignion_csv\Files;

/**
 * Expand a file pattern for every node based on a timeframe query.
 */
class NodeQueryFilePattern implements FilePatternInterface {

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info-array as defined in hook_campaignion_csv_info().
   * @param \DateTimeInterface $now
   *   The time considered to be now. Defaults to the date and time.
   */
  public static function fromInfo(array $info, \DateTimeInterface $now = NULL) {
    $node_query = <<<SQL
SELECT s.nid
FROM webform_submissions s
  INNER JOIN webform_component c USING(nid)
  INNER JOIN webform_submitted_data d USING(nid, sid, cid)
WHERE c.type='e2t_selector' AND s.submitted BETWEEN :start AND :end
GROUP BY s.nid
SQL;
    $date_pattern = $info['date_pattern_class']::fromInfo($info);
    return new static($info['path'], $node_query, $date_pattern, $info);
  }

  /**
   * Create a new instance.
   */
  public function __construct(string $path, string $node_query, DateIntervalFilePattern $date_pattern, array $info) {
    $this->pathPattern = $path;
    $this->datePattern = $date_pattern;
    $this->nodeQuery = $node_query;
    $this->info = $info;
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
    foreach ($this->datePattern->iterateTimeFrames() as $timeframe) {
      list($start, $end) = $timeframe->getTimestamps();
      foreach (db_query($this->nodeQuery, [':start' => $start, ':end' => $end])->fetchCol() as $nid) {
        $path = strftime(strtr($this->pathPattern, ['!nid' => $nid]), $start);
        $info = [
          'nid' => $nid,
          'path' => $root . '/' . $path,
          'timeframe' => $timeframe,
        ] + $this->info;
        $files[$path] = TimeframeFileInfo::fromInfo($info);
      }
    }
    return $files;
  }

}

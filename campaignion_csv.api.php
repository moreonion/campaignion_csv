<?php

/**
 * @file
 * Document hooks invoked by the campaignion_csv module.
 */

/**
 * Define file pattern and export formats.
 */
function hook_campaignion_csv_info() {
  $exports['actions'] = [
    'file_pattern' => [
      'class' => FilePattern::class,
      'path' => 'actions/%Y-%m.csv',
      'retention_period' => new \DateInterval('P6M'),
      'include_current' => TRUE,
    ],
    'exporter' => [
      'class' => ActionExporter::class,
    ],
  ];
  return $exports;
}

/**
 * Alter the exports defined in hook_campaignion_csv_info().
 *
 * @param array $exports
 *   The exports defined in the earlier hook invocation.
 */
function hook_campaignion_csv_info_alter(array &$exports) {
  $exports['actions']['path'] = 'my-actions/%Y-%m.csv';
}

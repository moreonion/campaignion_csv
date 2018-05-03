<?php

/**
 * Define file pattern and export formats.
 */
function hook_campaignion_csv_info() {
  $exports['actions'] = [
    'file_pattern' => [
      'class' => FilePattern::class,
      'path_pattern' => 'actions/%Y-%m.csv',
      'interval' => new \DateInterval('P1M'),
      'retention_period' => new \DateInterval('P6M'),
      'include_current' => TRUE,
    ],
    'exporter' => [
      'class' => ActionExporter::class,
    ],
  ];
  return $exports;
}

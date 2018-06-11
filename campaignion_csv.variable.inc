<?php

/**
 * @file
 * Configuration variable definitions and documentation.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_csv_variable_info() {
  $variables['campaignion_csv_path'] = [
    'type' => 'file_path',
    'title' => t('CSV export directory'),
    'description' => t('The campaignion CSV module manages its exported files in this directory.'),
    'default' => '',
  ];
  $variables['campaignion_csv_time_limit'] = [
    'type' => 'number',
    'title' => t('Soft time limit for cron-jobs'),
    'description' => t('The cron job doesn’t start new file exports after this time limit passes.'),
    'default' => 15 * 60,
  ];
  $variables['campaignion_csv_memory_limit'] = [
    'type' => 'number',
    'title' => t('Soft limit for leaked memory'),
    'description' => t('Some of the exports might leak memory. No new exports are started once this amount of memory has been leaked.'),
    'default' => 60 * 1024 * 1014,
  ];
  return $variables;
}

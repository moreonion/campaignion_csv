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
  return $variables;
}

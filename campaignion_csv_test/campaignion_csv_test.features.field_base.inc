<?php

/**
 * @file
 * campaignion_csv_test.features.field_base.inc
 */

/**
 * Implements hook_field_default_field_bases().
 */
function campaignion_csv_test_field_default_field_bases() {
  $field_bases = array();

  // Exported field_base: 'redhen_contact_email'.
  $field_bases['redhen_contact_email'] = array(
    'active' => 1,
    'cardinality' => -1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'redhen_contact_email',
    'indexes' => array(
      'redhen_email_address' => array(
        0 => 'value',
      ),
    ),
    'locked' => 0,
    'module' => 'redhen_fields',
    'settings' => array(
      'labels' => array(
        1 => 'home',
        2 => 'work',
      ),
    ),
    'translatable' => 0,
    'type' => 'redhen_email',
  );

  return $field_bases;
}

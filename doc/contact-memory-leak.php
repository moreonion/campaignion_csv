<?php

/**
 * @file
 * This a tiny script that makes the contact export memory leak reproducible.
 *
 * The script can be run using `drush scr` in any campaignion site with at least
 * a few thousand contacts. You’ll see a tiny increases of the memory usage
 * while the script is running although the static caches are cleared after
 * each batch of 100 contacts.
 */

$last_id = 0;
while (TRUE) {
  $contact_ids = db_select('redhen_contact', 'c')
    ->fields('c', ['contact_id'])
    ->condition('type', 'contact')
    ->condition('contact_id', $last_id, '>')
    ->orderBy('contact_id')
    ->range(0, 100)
    ->execute()
    ->fetchCol();
  if (!$contact_ids) {
    break;
  }

  $contacts = entity_load('redhen_contact', $contact_ids, [], TRUE);
  foreach ($contacts as $contact) {
    // This line causes the memory leak although $item is immediately discarded
    // after its definition.
    $item = $contact->supporter_tags[LANGUAGE_NONE];
    $last_id = $contact->contact_id;
  }
  drupal_static_reset();
  gc_collect_cycles();
  $usage = memory_get_usage();
  echo "Memory usage: $usage.\n";
}

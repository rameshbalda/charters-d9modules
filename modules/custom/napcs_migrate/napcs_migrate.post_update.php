<?php

/**
 * @file
 * Post-update hooks for NAPCS migrate.
 */

/**
 * Delete splashify entities.
 */
function napcs_migrate_post_update_delete_splashify(&$sandbox) {
  $entity_type_manager = \Drupal::entityTypeManager();
  foreach ([
    'splashify_entity',
    'splashify_group_entity',
  ] as $entity_type) {
    foreach ($entity_type_manager->getStorage($entity_type)->loadMultiple() as $entity) {
      $entity->delete();
    }
  }
}

/**
 * Clean up kint module.
 */
function napcs_migrate_post_update_clean_kint(&$sandbox) {
  \Drupal::keyValue('system.schema')->delete('kint');
}

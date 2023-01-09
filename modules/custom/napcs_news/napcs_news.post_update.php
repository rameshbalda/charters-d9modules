<?php

/**
 * @file
 * napcs_news.post_update.php
 */

use Drupal\Core\Utility\UpdateException;

/**
 * Merge Media Advisory and Statement into Press Release.
 */
function napcs_news_post_update_news_merge(&$sandbox) {
  $entity_type_manager = \Drupal::entityTypeManager();
  $term_storage = $entity_type_manager->getStorage('taxonomy_term');

  $src_terms = $term_storage->loadByProperties([
    'name' => [
      'Media Advisory',
      'Statement',
    ],
  ]);
  if (!$src_terms) {
    throw new UpdateException("No source terms found");
  }
  if (count($src_terms) > 2) {
    throw new UpdateException("Unexpected number of source terms found");
  }

  $dest_terms = $term_storage->loadByProperties(['name' => 'Press Release']);
  if (!$dest_terms) {
    throw new UpdateException("No destination term found");
  }
  $dest_term_id = reset($dest_terms)->id();

  $news_type_field = 'field_news_item_types';
  $nodes = $entity_type_manager->getStorage('node')->loadByProperties([
    $news_type_field => array_map(function ($term) {
      return $term->id();
    }, $src_terms),
  ]);
  if (!$nodes) {
    throw new UpdateException("No nodes found");
  }
  foreach ($nodes as $node) {
    $node->$news_type_field->target_id = $dest_term_id;
    $node->save();
  }

  foreach ($src_terms as $src_term) {
    $src_term->delete();
  }
}

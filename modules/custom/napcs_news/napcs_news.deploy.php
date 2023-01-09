<?php

/**
 * @file
 * napcs_news.post_update.php
 */

use Drupal\Core\Utility\UpdateException;

/**
 * Migrate video embed fields to core media.
 */
function napcs_news_deploy_core_video(&$sandbox) {
  $media_source_field = 'field_media_oembed_video';
  $entity_type_manager = \Drupal::entityTypeManager();
  $media_storage = $entity_type_manager->getStorage('media');
  $embed_fields = $entity_type_manager->getStorage('field_storage_config')
    ->loadByProperties([
      'type' => 'video_embed_field',
    ]);
  $count = 0;
  foreach ($embed_fields as $embed_field) {
    $field_name = $embed_field->getName();
    $media_field = $field_name . '_media';

    $entity_storage = $entity_type_manager
      ->getStorage($embed_field->getTargetEntityTypeId());
    $ids = $entity_storage->getQuery()->accessCheck(FALSE)->exists($field_name)
      ->execute();
    $entities = $entity_storage->loadMultiple($ids);

    foreach ($entities as $entity) {
      $video_url = $entity->$field_name->value;
      $media_data = [$media_source_field => $video_url];
      $existing_media = $media_storage->loadByProperties($media_data);
      list($media) = $existing_media;
      if ($media) {
        if ($entity->$media_field->target_id == $media->id()) {
          continue;
        }
      }
      else {
        $media_data += ['bundle' => 'remote_video'];
        $media = $media_storage->create($media_data);
        $media->save();
      }
      $entity->$media_field->setValue([['target_id' => $media->id()]]);
      $entity->save();
      $count++;
    }
  }
  return t("$count entities updated");
}

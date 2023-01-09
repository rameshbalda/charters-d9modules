<?php

namespace Drupal\napcs_model_law\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;

/**
 * Migrate nodes without blank source data overwriting existing data.
 *
 * @MigrateDestination(
 *   id = "node_safe"
 * )
 */
class NodeSafe extends EntityContentBase {

  /**
   * Finds the entity type from configuration or plugin ID.
   *
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @return string
   *   The entity type.
   *
   * @see Drupal\migrate\Plugin\migrate\destination\Entity::getEntityTypeId()
   */
  protected static function getEntityTypeId($plugin_id) {
    // Remove "entity:".
    // return substr($plugin_id, 7);
    // We just need "node".
    return 'node';
  }

  /**
   * Updates an entity with the new values from row.
   *
   * This is copied from EntityContentBase for the purpose of removing the lines
   * that enforce the migration of blank values. We want blank values to
   * indicate fields that should be untouched.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to update.
   * @param \Drupal\migrate\Row $row
   *   The row object to update from.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   An updated entity, or NULL if it's the same as the one passed in.
   *
   * @see Drupal\migrate\Plugin\migrate\destination\EntityConfigBase::updateEntity()
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    $empty_destinations = $row->getEmptyDestinationProperties();
    // By default, an update will be preserved.
    $rollback_action = MigrateIdMapInterface::ROLLBACK_PRESERVE;

    // Make sure we have the right translation.
    if ($this->isTranslationDestination()) {
      $property = $this->storage->getEntityType()->getKey('langcode');
      if ($row->hasDestinationProperty($property)) {
        $language = $row->getDestinationProperty($property);
        if (!$entity->hasTranslation($language)) {
          $entity->addTranslation($language);

          // We're adding a translation, so delete it on rollback.
          $rollback_action = MigrateIdMapInterface::ROLLBACK_DELETE;
        }
        $entity = $entity->getTranslation($language);
      }
    }

    // If the migration has specified a list of properties to be overwritten,
    // clone the row with an empty set of destination values, and re-add only
    // the specified properties.
    if (isset($this->configuration['overwrite_properties'])) {
      $empty_destinations = array_intersect($empty_destinations, $this->configuration['overwrite_properties']);
      $clone = $row->cloneWithoutDestination();
      foreach ($this->configuration['overwrite_properties'] as $property) {
        $clone->setDestinationProperty($property, $row->getDestinationProperty($property));
      }
      $row = $clone;
    }

    foreach ($row->getDestination() as $field_name => $values) {
      $field = $entity->$field_name;
      $is_field = $field instanceof TypedDataInterface;
      // Some backflips to avoid updating the body field when there is no value,
      // since for that field $values['format'] will always be 'full_html', and
      // thus doesn't make it into $empty_destinations.
      $is_body_field = $field_name == 'body';
      $has_body_value = ($is_body_field && isset($values['value']));
      if ($has_body_value || !$is_body_field && $is_field) {
        $field->setValue($values);
      }
    }
    // Here is where empty fields would be set to null, like this:
    // foreach ($empty_destinations as $field_name) {
    //   $entity->$field_name = NULL;
    // }
    // We're not doing that.
    $this->setRollbackAction($row->getIdMap(), $rollback_action);

    // We might have a different (translated) entity, so return it.
    return $entity;
  }

}

<?php

namespace Drupal\napcs_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Return a file id corresponding to a URI.
 *
 * @code
 * process:
 *   plugin: document_by_uri
 *   source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "document_by_uri"
 * )
 */
class DocumentByUri extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $query = \Drupal::entityQuery('media');
    $query->condition('field_file.entity.uri', $value);
    $result = $query->execute();
    $document_id = array_shift($result);
    return $document_id;
  }

}

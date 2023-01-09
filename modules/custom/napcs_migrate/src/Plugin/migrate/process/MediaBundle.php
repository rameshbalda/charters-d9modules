<?php

namespace Drupal\napcs_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Determine media bundle from filename.
 *
 * @MigrateProcessPlugin(
 *   id = "media_bundle"
 * )
 */
class MediaBundle extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (preg_match('/pdf$/', $value)) {
      return 'document';
    }
    return 'image';
  }

}

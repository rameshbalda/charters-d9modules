<?php

namespace Drupal\napcs_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Strip slashes from right side of input string.
 *
 * @code
 * process:
 *   plugin: strip_trailing_slashes
 *   source: foo
 * @endcode
 *
 * "/path/to/whatever/" becomes "/path/to/whatever".
 *
 * @MigrateProcessPlugin(
 *   id = "strip_trailing_slash"
 * )
 */
class StripTrailingSlash extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return rtrim($value, '/');
  }

}

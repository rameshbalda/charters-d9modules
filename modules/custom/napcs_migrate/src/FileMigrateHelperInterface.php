<?php

namespace Drupal\napcs_migrate;

/**
 * Interface FileMigrateHelperInterface.
 */
interface FileMigrateHelperInterface {

  /**
   * Download file at $src and return the URL of its new location.
   *
   * @param string $src
   *   The URL of the source file.
   *
   * @return FileInterface|bool
   *   The URL of the new file, or FALSE if download failed.
   */
  public function getFile($src);

}

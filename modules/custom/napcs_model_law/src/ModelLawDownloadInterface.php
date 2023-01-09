<?php

namespace Drupal\napcs_model_law;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for model law data downloads.
 */
interface ModelLawDownloadInterface extends PluginInspectionInterface {

  /**
   * Return the path of a file that can be downloaded.
   *
   * @see file_unmanaged_save_data
   *
   * @return string
   *   A file path.
   */
  public function download();

}

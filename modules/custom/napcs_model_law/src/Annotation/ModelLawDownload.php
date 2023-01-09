<?php

namespace Drupal\napcs_model_law\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an NAPCS Model Law download plugin annotation object.
 *
 * Plugin Namespace: Plugin\napcs_model_law\ModelLawDownload.
 *
 * @see \Drupal\napcs_model_law\ModelLawDownloadManager
 * @see plugin_api
 *
 * @Annotation
 */
class ModelLawDownload extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the plugin.
   *
   * @var string
   */
  public $name;

}

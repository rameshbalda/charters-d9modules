<?php

namespace Drupal\napcs_model_law\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an NAPCS Model Law data provider plugin annotation object.
 *
 * Plugin Namespace: Plugin\napcs_model_law\ModelLawDataProvider.
 *
 * @see \Drupal\napcs_model_law\ModelLawDataProviderManager
 * @see plugin_api
 *
 * @Annotation
 */
class ModelLawDataProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

}

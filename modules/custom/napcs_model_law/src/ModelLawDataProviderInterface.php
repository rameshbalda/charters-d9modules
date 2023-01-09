<?php

namespace Drupal\napcs_model_law;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for model law data providers.
 */
interface ModelLawDataProviderInterface extends PluginInspectionInterface {

  /**
   * Return panel render arrays.
   */
  public function getPanels();

}

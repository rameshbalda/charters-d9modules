<?php

namespace Drupal\napcs_model_law;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * ModelLawDataProvider plugin manager.
 */
class ModelLawDataProviderManager extends DefaultPluginManager {

  /**
   * Constructs an ModelLawDataProviderManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ModelLawDataProvider', $namespaces, $module_handler, 'Drupal\napcs_model_law\ModelLawDataProviderInterface', 'Drupal\napcs_model_law\Annotation\ModelLawDataProvider');

    $this->alterInfo('napcs_model_law_data_provider_info');
    $this->setCacheBackend($cache_backend, 'napcs_model_law_data_providers');
  }

}

<?php

namespace Drupal\napcs_model_law;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * ModelLawDownload plugin manager.
 */
class ModelLawDownloadManager extends DefaultPluginManager {

  /**
   * Constructs an ModelLawDownloadManager object.
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
    parent::__construct('Plugin/ModelLawDownload', $namespaces, $module_handler, 'Drupal\napcs_model_law\ModelLawDownloadInterface', 'Drupal\napcs_model_law\Annotation\ModelLawDownload');

    $this->alterInfo('napcs_model_law_download_info');
    $this->setCacheBackend($cache_backend, 'napcs_model_law_downloads');
  }

}

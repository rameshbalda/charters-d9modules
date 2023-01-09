<?php

namespace Drupal\napcs_model_law\Plugin\Block;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\napcs_model_law\ModelLawData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Model Law Components accordion block.
 *
 * @Block(
 *   id="model_law_data",
 *   admin_label=@Translation("Model Law Data"),
 * )
 */
class ModelLawDataBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $route_match = $container->get('current_route_match');
    $data_provider_manager = $container->get('plugin.manager.model_law_data_provider');
    $model_law_data = $container->get('napcs_model_law.data');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $route_match,
      $data_provider_manager,
      $model_law_data
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $route_match, PluginManagerInterface $data_provider_manager, ModelLawData $model_law_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $current_node = $route_match->getParameter('node');
    $this->currentNode = $current_node;
    $this->dataProvider = $current_node ? $data_provider_manager->createInstance($current_node->getType()) : NULL;
    $this->modelLawData = $model_law_data;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    if ($this->dataProvider) {
      $build = [
        '#theme' => 'model_law_accordion',
        '#panels' => $this->dataProvider->getPanels(),
      ];
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $current_node = $this->currentNode;
    $tags = Cache::mergeTags(parent::getCacheTags(), ['node:' . $current_node->id()]);
    $related_methods = [
      'getChildren',
      'getScoreNodes',
      'getStatusNodes',
    ];
    foreach ($related_methods as $method) {
      $related_nodes = $this->modelLawData->{$method}($current_node);
      $related_tags = array_map(function ($node) {
        return 'node:' . $node->id();
      }, $related_nodes);
      $tags = Cache::mergeTags($tags, $related_tags);
    }
    return $tags;
  }

}

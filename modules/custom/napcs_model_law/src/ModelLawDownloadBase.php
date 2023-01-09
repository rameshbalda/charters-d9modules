<?php

namespace Drupal\napcs_model_law;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use League\Csv\Writer;

/**
 * Base class for Model Law downloads.
 */
class ModelLawDownloadBase extends PluginBase implements ModelLawDownloadInterface, ContainerFactoryPluginInterface {

  /**
   * Class constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $node_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $node_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

  /**
   * Stub for generating data.
   */
  protected function generate() {
    throw new Exception('You must override ModelLawDownloadBase::generate().');
  }

  /**
   * Return the path of a generated template.
   */
  public function download() {
    $rows = $this->generate();
    if (!is_array($rows)) {
      throw new \Exception($rows);
    }
    $path = $this->writeCsv($rows);
    return $path;
  }

  /**
   * Write an array of rows as a CSV file and return its path.
   */
  protected function writeCsv($rows) {
    $csv = Writer::createFromString('');
    $csv->insertAll($rows);
    $path = \Drupal::service('file_system')->saveData($csv->toString(), 'temporary://');
    return $path;
  }

  /**
   * Load nodes of a given type.
   */
  protected function loadNodesByType($type) {
    return $this->nodeStorage->loadByProperties(compact('type'));
  }

  /**
   * Load nodes by properties.
   */
  protected function loadNodeByProperties($properties) {
    $nodes = $this->nodeStorage->loadByProperties($properties);
    return reset($nodes);
  }

}

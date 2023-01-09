<?php

namespace Drupal\napcs_migrate\Plugin\migrate\process;

use Drupal\napcs_migrate\DOMHelperInterface;
use Drupal\napcs_migrate\FileMigrateHelperInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'HubspotImage' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "hubspot_image"
 * )
 */
class HubspotImage extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The DOM helper service.
   *
   * @var Drupal\napcs_migrate\DOMHelperInterface
   */
  protected $dom;

  /**
   * The entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The file migrate helper service.
   *
   * @var Drupal\napcs_migrate\FileMigrateHelperInterface
   */
  protected $fileHelper;

  /**
   * Class constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    DOMHelperInterface $dom_helper,
    EntityTypeManagerInterface $entity_type_manager,
    FileMigrateHelperInterface $file_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dom = $dom_helper;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileHelper = $file_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('napcs_migrate.dom_helper'),
      $container->get('entity_type.manager'),
      $container->get('napcs_migrate.file_migrate_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Get file corresponding to blog post url.
    $file_location = $this->dom->getHtmlFileLocation($value);
    // Load up DOM.
    $page_dom = $this->dom->loadHtmlFile($file_location);
    if (!$page_dom) {
      return FALSE;
    }
    // Find featured image element.
    $dom_finder = new \DomXPath($page_dom);
    $img_element = $dom_finder->query("//img[contains(@class, 'post-featured-image')]");
    if (!$img_element->length) {
      return FALSE;
    }
    // Get url of featured image.
    $src = $img_element->item(0)->getAttribute('src');
    if (!$src) {
      return FALSE;
    }
    // Copy featured image to local filesystem as managed file.
    $dest = $this->fileHelper->getFileDestination($src);
    $file = $this->fileHelper->retrieveFile($src, $dest);
    if (!$file) {
      return FALSE;
    }
    // Create media entity with local file.
    $media = $this->entityTypeManager->getStorage('media')->create([
      'bundle' => 'image',
      'field_image' => $file,
    ]);
    $media->save();
    return $media->id();
  }

}

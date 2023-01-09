<?php

namespace Drupal\napcs_migrate\Plugin\migrate\process;

use Drupal\napcs_migrate\DOMHelperInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Row;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gets the source value.
 *
 * @MigrateProcessPlugin(
 *   id = "hubspot_content"
 * )
 */
class HubspotContent extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The DOM helper service.
   *
   * @var Drupal\napcs_migrate\DOMHelperInterface
   */
  protected $dom;

  /**
   * Class constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DOMHelperInterface $dom_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dom = $dom_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('napcs_migrate.dom_helper')
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
      print "DOM could not be loaded from $file_location\n";
      throw new MigrateSkipRowException();
    }
    // Find body-wrapping element.
    $body_element = $page_dom->getElementById('hs_cos_wrapper_post_body');
    // Process quirks, including downloading hosted `img`s.
    $this->dom->processChildren($body_element);
    // Render body content back to string.
    $output = $this->dom->renderChildren($body_element);
    return $output;
  }

}

<?php

namespace Drupal\napcs_migrate\Plugin;

use Drupal\napcs_migrate\DOMHelperInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for DOMElement process plugins.
 */
abstract class DOMElementProcessBase extends PluginBase implements DOMElementProcessInterface, ContainerFactoryPluginInterface {

  /**
   * The DOM Helper service.
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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_interface) {
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
  abstract public function process(\DOMNode $element);

}

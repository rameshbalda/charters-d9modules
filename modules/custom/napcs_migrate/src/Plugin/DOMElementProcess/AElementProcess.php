<?php

namespace Drupal\napcs_migrate\Plugin\DOMElementProcess;

use Drupal\napcs_migrate\DOMHelperInterface;
use Drupal\napcs_migrate\FileMigrateHelperInterface;
use Drupal\napcs_migrate\Plugin\DOMElementProcessBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process an `a` tag.
 *
 * @DOMElementProcess(
 *   id = "a",
 *   label = "`a` element"
 * )
 */
class AElementProcess extends DOMElementProcessBase {

  /**
   * The file migrate helper service.
   *
   * @var Drupal\napcs_migrate\FileMigrateHelperInterface
   */
  protected $fileMigrate;

  /**
   * Class constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DOMHelperInterface $dom_helper, FileMigrateHelperInterface $file_migrate_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $dom_helper);
    $this->fileMigrate = $file_migrate_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_interface) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('napcs_migrate.dom_helper'),
      $container->get('napcs_migrate.file_migrate_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(\DOMNode $element) {
    // Strip inline styles.
    if ($element->hasAttribute('style')) {
      $element->removeAttribute('style');
    }
    // Fix urls.
    if ($href = $element->getAttribute('href')) {
      $host = parse_url($href, PHP_URL_HOST);
      $new_href = FALSE;
      if (is_null($host) || $host == 'blog.publiccharters.org') {
        $replace = [
          // Get rid of relative paths.
          '#(\.\./)*\.\.#' => '',
          '#org/publiccharters/blog/(.*)\.html#' => '$1',
          // Change blog.publiccharters.org => publiccharters.org.
          '#//blog\.#' => 'https://',
        ];
        $new_href = preg_replace(array_keys($replace), array_values($replace), $href);
      }
      // Download links hosted by hubspot.
      if ($host == 'cdn2.hubspot.net') {
        $new_href = $this->fileMigrate->getFile($href, $destination);
      }
      if ($new_href) {
        $element->setAttribute('href', $new_href);
      }
    }

    // Replace strongs with their children.
    for ($i = $element->childNodes->length; --$i >= 0;) {
      $child = $element->childNodes->item($i);
      if ($child->nodeName == 'strong') {
        while ($grandchild = $child->childNodes->item(0)) {
          $element->insertBefore($grandchild, $child);
        }
        $child->parentNode->removeChild($child);
      }
    }
  }

}

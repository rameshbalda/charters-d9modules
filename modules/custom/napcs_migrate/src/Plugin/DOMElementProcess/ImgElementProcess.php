<?php

namespace Drupal\napcs_migrate\Plugin\DOMElementProcess;

use Drupal\napcs_migrate\DOMHelperInterface;
use Drupal\napcs_migrate\FileMigrateHelperInterface;
use Drupal\napcs_migrate\Plugin\DOMElementProcessBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process an `img` tag.
 *
 * @DOMElementProcess(
 *   id = "img",
 *   label = "`img` element"
 * )
 */
class ImgElementProcess extends DOMElementProcessBase {

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
    if ($src = $this->getSrc($element)) {
      if ($new_src = $this->fileMigrate->getFile($src)) {
        $element->setAttribute('src', $new_src);
        $element->setAttribute('class', 'img-responsive');
        $element->removeAttribute('srcset');
        $element->removeAttribute('height');
        $element->removeAttribute('width');
        $element->removeAttribute('sizes');
      }
    }
  }

  /**
   * Get the best value for src from an `img` element.
   *
   * @param \DOMNode $element
   *   The `img` element.
   *
   * @return string
   *   A URL to an image.
   */
  protected function getSrc(\DOMNode $element) {
    $src = $this->getMaxResSrc($element) ?? $element->getAttribute('src');
    $host = parse_url($src, PHP_URL_HOST);
    if ($host == 'cdn2.hubspot.net') {
      return $src;
    }
    return FALSE;
  }

  /**
   * Get the maximum-resolution source from $element.
   *
   * @param \DOMNode $element
   *   The DOM element.
   *
   * @return string
   *   The URL of the maximum resolution source.
   */
  protected function getMaxResSrc(\DOMNode $element) {
    $src = NULL;
    if ($srcset = $element->getAttribute('srcset')) {
      $srcset = explode(', ', $srcset);
      $res_src_map = array_reduce($srcset, function ($res_src_map, $src) {
        list($url, $width) = explode(' ', $src);
        $width = str_replace('w', '', $width);
        $res_src_map[$width] = $url;
        return $res_src_map;
      }, []);
      ksort($res_src_map);
      $src = array_pop($res_src_map);
    }
    return $src;
  }

}

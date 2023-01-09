<?php

namespace Drupal\napcs_migrate;

use Drupal\Core\File\FileSystemInterface;
use Drupal\napcs_migrate\Plugin\DOMElementProcessManager;

/**
 * Class DOMHelperService.
 */
class DOMHelperService implements DOMHelperInterface {

  /**
   * The file system service.
   *
   * @var Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Drupal\napcs_migrate\Plugin\DOMElementProcessManager definition.
   *
   * @var \Drupal\napcs_migrate\Plugin\DOMElementProcessManager
   */
  protected $processPluginManager;

  /**
   * Constructs a new DOMHelperService object.
   */
  public function __construct(FileSystemInterface $file_system, DOMElementProcessManager $process_plugin_manager) {
    $this->fileSystem = $file_system;
    $this->processPluginManager = $process_plugin_manager;
  }

  /**
   * Return a uri for the file corresponding to the blog post at $url.
   *
   * @param string $url
   *   The URL of the blog post.
   *
   * @return string
   *   The URI of the HTML file of the blog post.
   */
  public function getHtmlFileLocation($url) {
    // Replace trailing slash with an underscore.
    $path = preg_replace('#/$#', '_', parse_url($url, PHP_URL_PATH));
    return $this->fileSystem->realpath("private://hubspot-blog$path.html");
  }

  /**
   * {@inheritdoc}
   */
  public function loadHtmlFile($file_location) {
    return @\DOMDocument::loadHTMLFile($file_location);
  }

  /**
   * {@inheritdoc}
   */
  public function processChildren(\DOMNode $element) {
    foreach ($this->processPluginManager->getDefinitions() as $tag => $definition) {
      $tag_elements = $element->getElementsByTagName($tag);
      if ($tag_elements->length) {
        $process = $this->processPluginManager->createInstance($tag);
        for ($i = $tag_elements->length; --$i >= 0;) {
          $process->process($tag_elements->item($i));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function renderChildren(\DOMNode $element) {
    $output = '';
    foreach ($element->childNodes as $child) {
      $output .= $element->ownerDocument->saveHtml($child);
    }
    return $output;
  }

}

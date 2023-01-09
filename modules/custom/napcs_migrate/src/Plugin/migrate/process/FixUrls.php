<?php

namespace Drupal\napcs_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Fix urls of internal links and image sources.
 *
 * @code
 * process:
 *   plugin: fix_urls
 *   source: foo
 * @endcode
 *
 * Absolute links become relative, and any URLs with paths that start with
 * '/wp-content/uploads' get '/sites/default/files/migrated' prepended.
 *
 * @MigrateProcessPlugin(
 *   id = "fix_urls"
 * )
 */
class FixUrls extends ProcessPluginBase {

  /**
   * An associated array whose keys are HTML tag names and whose values are
   * the attribute to be checked for that tag.
   */
  protected $fixTargets;

  /**
   * A DOMDocument representing the parsed value to be transformed.
   */
  protected $htmlDom;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fixTargets = [
      'img' => 'src',
      'a' => 'href',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($value) {
      $this->htmlLoad($value);
      // Scan HTML tree for elements to fix URLs for.
      foreach ($this->fixTargets as $tag => $attr) {
        $this->fixElements($tag, $attr);
      }
      // Render DOMDocument (including mutated elements) back to string.
      $value = $this->htmlDom->saveHTML();
    }
    return $value;
  }

  /**
   * Load value into DOMDOcument.
   */
  protected function htmlLoad($value) {
    $this->htmlDom = new \DOMDocument();
    $value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
    $this->htmlDom->loadHTML($value, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
  }

  /**
   * Find all elements of a given tag name and fix their attributes.
   */
  protected function fixElements($tag, $attr) {
    $elements = $this->getElements($tag);
    foreach ($elements as $element) {
      $src = trim($element->getAttribute($attr));
      $url = $this->fixUrl($src);
      if ($url != $src) {
        $element->setAttribute($attr, $url);
      }
    }
  }

  /**
   * Return a DOMNodeList of elements of a given tag name.
   */
  protected function getElements($tag) {
    $elements = $this->htmlDom->getElementsByTagName($tag);
    return $elements;
  }

  /**
   * Return a transformed URL.
   */
  protected function fixUrl($src) {
    $url = $src;
    $url_parts = parse_url($url);

    // URL is absolute if path is set.
    $is_absolute = isset($url_parts['host']);
    // URL is internal if not absolute (i.e. relative) or has publiccharters.org
    // as host.
    $is_internal = !$is_absolute || preg_match('/^(www.)?publiccharters.org/', $url_parts['host']);

    // Process internal URLs.
    if ($is_internal) {
      $path = $url_parts['path'];
      // URL is file if path starts with /wp-content/upload.
      $is_file = strpos($path, '/wp-content/upload') === 0;
      // If URL is file, prepend new migrated file location to path.
      if ($is_file) {
        $path = '/sites/default/files/migrated' . $path;
      }
      // If URL is file or absolute, make sure it is relative (i.e. use only the
      // path) and add back any query or fragment.
      if ($is_file || $is_absolute) {
        $url = $path;
        if (isset($url_parts['query'])) {
          $url .= "?{$url_parts['query']}";
        }
        if (isset($url_parts['fragment'])) {
          $url .= "#{$url_parts['fragment']}";
        }
      }
    }
    return $url;
  }

}

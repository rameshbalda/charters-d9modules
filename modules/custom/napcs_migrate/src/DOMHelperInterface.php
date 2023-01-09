<?php

namespace Drupal\napcs_migrate;

/**
 * Interface DOMHelperInterface.
 */
interface DOMHelperInterface {

  /**
   * Return a DOMDocument representing the file at $file_location.
   *
   * @param string $file_location
   *   A full local path to a file.
   *
   * @return \DOMDocument
   *   A DOMDocument object with the file loaded.
   */
  public function loadHtmlFile($file_location);

  /**
   * Process the children of $element.
   *
   * @param \DOMNode $element
   *   The DOM element.
   */
  public function processChildren(\DOMNode $element);

  /**
   * Return a string with the rendered HTML of the children of $element.
   *
   * @param \DOMNode $element
   *   The element to render.
   *
   * @return string
   *   The rendered HTML of the element's children.
   */
  public function renderChildren(\DOMNode $element);

}

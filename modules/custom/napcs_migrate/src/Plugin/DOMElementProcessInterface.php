<?php

namespace Drupal\napcs_migrate\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for DOMElement process plugins.
 */
interface DOMElementProcessInterface extends PluginInspectionInterface {

  /**
   * Process a DOMElement.
   *
   * @param \DOMNode $element
   *   The element to process.
   */
  public function process(\DOMNode $element);

}

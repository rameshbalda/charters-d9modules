<?php

namespace Drupal\napcs_migrate\Plugin\DOMElementProcess;

use Drupal\napcs_migrate\Plugin\DOMElementProcessBase;

/**
 * Process a `strong` tag.
 *
 * @DOMElementProcess(
 *   id = "strong",
 *   label = "strong element"
 * )
 */
class StrongElementProcess extends DOMElementProcessBase {

  /**
   * {@inheritdoc}
   */
  public function process(\DOMNode $element) {
    // If the only child is an `a` replace this element with its child.
    if ($element->firstChild->nodeName == 'a') {
      $parent = $element->parentNode;
      while ($child = $element->childNodes->item(0)) {
        $parent->insertBefore($child, $element);
      }
      $parent->removeChild($element);
    }
  }

}

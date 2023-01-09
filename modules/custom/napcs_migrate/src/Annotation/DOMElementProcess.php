<?php

namespace Drupal\napcs_migrate\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a DOMElement process item annotation object.
 *
 * @see \Drupal\napcs_migrate\Plugin\DOMElementProcessManager
 * @see plugin_api
 *
 * @Annotation
 */
class DOMElementProcess extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}

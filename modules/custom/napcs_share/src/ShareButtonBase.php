<?php

namespace Drupal\napcs_share;

use Drupal\Core\Render\Element\Link;

/**
 * Provides a base class for share buttons.
 */
abstract class ShareButtonBase extends Link implements ShareButtonInterface {

  /**
   * Pre-render callback.
   *
   * Allows implementations to process various properties then passes the
   * element on to Link::preRenderLink().
   */
  public static function preRenderLink($element) {
    $button_class = static::buttonClass();
    if (isset($element['#options']['attributes']['class'])) {
      $element['#options']['attributes']['class'][] = $button_class;
      $element['#options']['attributes']['class'][] = 'share-button';
    }
    else {
      $element['#options']['attributes']['class'] = [$button_class, 'share-button'];
    }
    $element['#options']['absolute'] = TRUE;

    // Allow all processing to use unmodified $element.
    $title = static::title($element);
    $url = static::url($element);
    $attached = static::attached($element);

    $element['#title'] = $title;
    $element['#url'] = $url;
    $element['#attached'] = $attached;

    return parent::preRenderLink($element);
  }

}

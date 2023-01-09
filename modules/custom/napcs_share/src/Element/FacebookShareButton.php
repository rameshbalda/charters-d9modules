<?php

namespace Drupal\napcs_share\Element;

use Drupal\napcs_share\ShareButtonBase;

/**
 * Renders a Facebook share button.
 *
 * @RenderElement("napcs_share_facebook")
 */
class FacebookShareButton extends ShareButtonBase {

  /**
   * {@inheritdoc}
   */
  public static function buttonClass() {
    return 'share-facebook';
  }

  /**
   * {@inheritdoc}
   */
  public static function title(array $element) {
    return t('<i class="fa fa-facebook"></i><span class="sr-only">Share @title to Facebook</span>', ['@title' => $element['#title']]);
  }

  /**
   * {@inheritdoc}
   */
  public static function url(array $element) {
    // Facebook buttons don't need to process the URL, return as is.
    return $element['#url'];
  }

  /**
   * {@inheritdoc}
   */
  public static function attached(array $element) {
    // Contains setup for Facebook's SDK and click handler binding.
    $element['#attached']['library'][] = 'napcs_share/facebook';
    $element['#attached']['drupalSettings']['napcs_share']['facebookButtonClass'] = self::buttonClass();
    return $element['#attached'];
  }

}

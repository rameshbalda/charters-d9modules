<?php

namespace Drupal\napcs_share\Element;

use Drupal\Core\Url;
use Drupal\napcs_share\ShareButtonBase;

/**
 * Renders a Twitter share button.
 *
 * @RenderElement("napcs_share_twitter")
 */
class TwitterShareButton extends ShareButtonBase {

  /**
   * {@inheritdoc}
   */
  public static function buttonClass() {
    return 'share-twitter';
  }

  /**
   * {@inheritdoc}
   */
  public static function title(array $element) {
    return t('<i class="fa fa-twitter"></i><span class="sr-only">Share @title to Twitter</span>', ['@title' => $element['#title']]);
  }

  /**
   * {@inheritdoc}
   */
  public static function url(array $element) {
    // Twitter's SDK catches links out to twitter.com/intent/tweet, so we just
    // have to provide the correct arguments.
    $absolute_url = $element['#url']->setOption('absolute', TRUE)->toString();
    $twitter_url_options = [
      'query' => [
        'text' => $element['#title'],
        'url' => $absolute_url,
        'via' => 'charteralliance',
      ],
    ];
    return Url::fromUri('//twitter.com/intent/tweet', $twitter_url_options);
  }

  /**
   * {@inheritdoc}
   */
  public static function attached(array $element) {
    $element['#attached']['library'][] = 'napcs_share/twitter';
    return $element['#attached'];
  }

}

<?php

namespace Drupal\napcs_share;

/**
 * Provides an interface for share button implementations.
 */
interface ShareButtonInterface {

  /**
   * Return a class with which to decorate the share button link.
   *
   * @return string
   *   An HTML class.
   */
  public static function buttonClass();

  /**
   * Return the processed title for the share button link.
   *
   * @param array $element
   *   The render array.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   *   A string or MarkupInterface to be used as the content of the share
   *   button link.
   */
  public static function title(array $element);

  /**
   * Return the processed url for the share button link.
   *
   * @param array $element
   *   The render array.
   *
   * @return \Drupal\Core\Url
   *   A Url to be used as the href property of the share button link.
   */
  public static function url(array $element);

  /**
   * Return an array of attached assets for the share button link.
   *
   * @param array $element
   *   The render array.
   *
   * @return array
   *   An associative array compatible with
   *   \Drupal\Core\Render\AttachmentsResponseProcessorInterface::processAttachments().
   */
  public static function attached(array $element);

}

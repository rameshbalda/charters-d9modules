<?php

namespace Drupal\napcs_hubspot\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'HubspotBlogBlock' block.
 *
 * @Block(
 *  id = "hubspot_blog",
 *  admin_label = @Translation("Hubspot blog block"),
 * )
 */
class HubspotBlogBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'napcs_hubspot_blog_block',
      '#attached' => [
        'library' => [
          'napcs_hubspot/hubspot',
          'napcs_hubspot/blog',
        ],
      ],
    ];
  }

}

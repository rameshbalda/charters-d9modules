<?php

namespace Drupal\napcs_hubspot\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Hubspot-integrated email signup form.
 *
 * @Block(
 *   id="hubspot_signup",
 *   admin_label="Hubspot Signup",
 * )
 */
class HubspotSignupBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'napcs_hubspot_signup_block',
      '#attached' => [
        'library' => [
          'napcs_hubspot/hubspot',
          'napcs_hubspot/signup',
        ],
      ],
    ];
  }

}

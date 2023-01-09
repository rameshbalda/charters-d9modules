<?php

namespace Drupal\napcs_job_board\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Checks access for NAPCS Job Board user register page.
 */
class UserRegisterAccess implements AccessInterface {

  /**
   * Allow access if role from route is allowed.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(RouteMatchInterface $route_match) {
    $user_helper = \Drupal::service('napcs_job_board.user_helper');
    $user_role = $route_match->getParameter('user_role');
    $is_job_board_role = $user_helper->isJobBoardRole($user_role);
    return AccessResult::allowedIf($is_job_board_role);
  }

}

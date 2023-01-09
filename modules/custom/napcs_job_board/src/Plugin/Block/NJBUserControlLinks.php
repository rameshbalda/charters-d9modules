<?php

namespace Drupal\napcs_job_board\Plugin\Block;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\napcs_job_board\NJBBlockBase;
use Drupal\napcs_job_board\NJBRoute;
use Drupal\user\Entity\Role;

/**
 * Provides a panel for user control actions in the NAPCS Job board.
 *
 * @Block(
 *   id = "njb_user_control_links",
 *   admin_label = @Translation("NJB User Control Links"),
 *   category = @Translation("Links"),
 * )
 */
class NJBUserControlLinks extends NJBBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'njb_user_control_links',
      '#title' => $this->getTitle(),
      '#NJBUserLinks' => $this->getLinks(),
      '#cache' => [
        'contexts' => ['url', 'user'],
        'tags' => ['node_list'],
      ],
    ];
  }

  /**
   * Return a render array for the title.
   *
   * @return array
   *   A render array.
   */
  protected function getTitle() {
    $user = $this->currentUser;
    if ($user->isAuthenticated()) {
      return [
        '#prefix' => '<div class="group-title">',
        '#markup' => t('Logged in as: @user', ['@user' => $user->getDisplayName()]),
        '#suffix' => '</div>',
        '#cache' => [
          'contexts' => ['user'],
        ],
      ];
    }
    return NULL;
  }

  /**
   * Return an array of link objects for user control actions.
   *
   * @return array
   *   An array of user control links.
   */
  protected function getLinks() {
    $user = $this->currentUser;
    if (!$this->routeHelper->isListingsPage()) {
      // This will pick up a back parameter and pass it to our link
      // so that we return exactly to the view we were on.
      $req = $this->getRequestValue('back');
      $options = [];
      if (!empty($req)) {
        $options['query'] = $req;
      }
      $links['back'] = $this->linkRoute('Back to job listings', NJBRoute::LISTINGS, [], $options);
    }

    if ($user->isAuthenticated()) {
      // Profile links.
      if ($user->hasPermission('create profile content')) {
        // User has profile.
        if ($profile = $this->userHelper->getUserProfile($user, NULL)) {
          // View profile.
          if (!$this->routeHelper->isNode($profile)) {
            $links['profile_view_link'] = $this->linkUrl('View profile', $profile->toUrl());
          }
          // Edit profile.
          if (!$this->routeHelper->isNode($profile, 'edit_form')) {
            $links['profile_edit_link'] = $this->linkUrl('Edit profile', $profile->toUrl('edit-form'));
          }
        }
        // User has no profile.
        elseif (!$this->routeHelper->isNodeAdd('profile') && !$this->routeHelper->isCurrentUser('edit_form')) {
          // Create profile.
          $links['profile_add_link'] = $this->linkRoute('Create profile', 'node.add', ['node_type' => 'profile']);
        }
      }
      if ($user->hasPermission('create job_listing content') && $this->userHelper->getOrganizations($user)) {
        // Employer dashboard "Manage Listings".
        if (!$this->routeHelper->isCurrentUser()) {
          $links['employer_link'] = $this->linkUrl('Manage listings', $user->toUrl());
        }
        // Add a new job listing.
        if (!$this->routeHelper->isNodeAdd('job_listing')) {
          $links['employer_add_link'] = $this->linkRoute('Add a new job listing', 'node.add', ['node_type' => 'job_listing']);
        }
      }
      // Add a new organization.
      if ($user->hasPermission('create organization content')) {
        if (!$this->routeHelper->isNodeAdd('organization')) {
          $links['employer_add_org_link'] = $this->linkRoute('Add a new organization', 'node.add', ['node_type' => 'organization']);
        }
      }
      // Logout.
      $logout_options = $this->getDestinationOption('logout');
      $links['logout'] = $this->linkRoute('Log out', 'user.logout', [], $logout_options);
    }
    else {
      // Register as employer and/or job seeker.
      foreach ($this->userHelper->getJobBoardRoles() as $role_name) {
        if (!$this->routeHelper->isRegisterPage($role_name)) {
          $user_role = Role::load($role_name);
          $register_text = t('Register as @user_role', ['@user_role' => $user_role->label()]);
          $links["register_$role_name"] = $this->linkRoute($register_text, NJBRoute::REGISTER, ['user_role' => $role_name]);
        }
      }
      // Login.
      $is_login_page = $this->routeHelper->isLoginPage();
      if (!$is_login_page) {
        $login_options = $this->getDestinationOption();
        $links['login_link'] = $this->linkRoute('Log in', NJBRoute::LOGIN, [], $login_options);
      }
      // Reset password.
      if ($is_login_page || $this->routeHelper->isRegisterPage()) {
        $links['reset_pass'] = $this->linkRoute('Forgot password?', NJBRoute::PASSWORD);
      }
    }
    return $links;
  }

  /**
   * Return a link created from a url.
   *
   * @param string $text
   *   The text for the link.
   * @param Drupal\Core\Url $url
   *   The url for the link.
   * @param array $options
   *   (optional) The options for the link.
   *
   * @return Drupal\Core\Link
   *   A link object, with button classes.
   *
   * @see Drupal\Core\Link::fromTextAndUrl()
   */
  protected function linkUrl($text, Url $url, array $options = []) {
    $this->addButtonClasses($url);
    return Link::fromTextAndUrl($text, $url, $options);
  }

  /**
   * Return a link created from a route.
   *
   * @param string $text
   *   The text for the link.
   * @param string $route_name
   *   The route name.
   * @param array $route_parameters
   *   An associative array of parameter names and values.
   * @param array $options
   *   (optional) The options for the link.
   *
   * @return Drupal\Core\Link
   *   A link object, with button classes.
   *
   * @see Drupal\Core\Link::createFromRoute()
   */
  protected function linkRoute($text, $route_name, array $route_parameters = [], array $options = []) {
    $url = Url::fromRoute($route_name, $route_parameters);
    $this->addButtonClasses($url);
    return Link::fromTextAndUrl($text, $url, $options);
  }

  /**
   * Add button classes to a url.
   *
   * @param Drupal\Core\Url $url
   *   The url.
   */
  protected function addButtonClasses(Url $url) {
    $url->setOption('attributes', [
      'class' => [
        'btn',
        'btn-default',
        'navbar-btn',
      ],
    ]);
  }

  /**
   * I don't really know.
   */
  private function getDestinationOption($type = 'login') {
    $options = [];
    $destination = $this->request->getPathInfo();
    // If type is logout and we will get redirected back to page where an
    // anonymous user doesn't have access to, then we want to set the
    // destination equal to our napcs-jobs-board landing search page.
    $failing = FALSE;
    if (!$this->routeHelper->routeNameMatches('user.logout')) {
      if ($this->routeHelper->isCurrentUser()
      || $this->routeHelper->isNodeAdd('application')) {
        $destination = '/job-board';
        $failing = TRUE;
      }
    }

    if (!$failing) {
      // Lets get all of the parameters.
      $allParams = $this->getAllRequestParameters();
      $queryString = $this->request->getQueryString();
      if (!empty($queryString)) {
        $destination .= '?' . $queryString;
      }
    }

    $options['query'] = [
      'destination' => $destination,
    ];
    return $options;
  }

}

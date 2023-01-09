<?php

namespace Drupal\napcs_job_board;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;

/**
 * Base class for Job Board blocks.
 */
abstract class NJBBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  protected $currentUser;
  protected $request;
  protected $currentRoute;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $account, RequestStack $request, ResettableStackedRouteMatchInterface $route, NJBRouteHelper $route_helper, NJBUserHelper $user_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentUser = User::load($account->id());
    $this->request = $request->getCurrentRequest();
    $this->currentRoute = $route;
    $this->routeHelper = $route_helper;
    $this->userHelper = $user_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('request_stack'),
      $container->get('current_route_match'),
      $container->get('napcs_job_board.route_helper'),
      $container->get('napcs_job_board.user_helper')
    );
  }

  /**
   * Gets from the current url a query paramter.
   *
   * @param string $name
   *   A key to get.
   *
   * @return object
   *   Returns a Drupal value format.
   */
  public function getRequestValue(string $name) {
    return $this->getRequest()->get($name);
  }

  /**
   * Return an array of request parameters.
   */
  public function getAllRequestParameters() {
    return $this->getRequest()->query->all();
  }

  /**
   * Gets the current request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The Symfony request object.
   */
  public function getRequest() {
    return $this->request;
  }

}

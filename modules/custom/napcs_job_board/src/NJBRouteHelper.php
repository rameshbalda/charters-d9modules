<?php

namespace Drupal\napcs_job_board;

use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxy;

/**
 * Class NJBRouteHelper.
 */
class NJBRouteHelper {

  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Constructs a new NJBRouteHelper object.
   */
  public function __construct(CurrentRouteMatch $current_route_match, AccountProxy $current_user) {
    $this->currentRouteMatch = $current_route_match;
    $this->currentUser = $current_user;
  }

  /**
   * Return TRUE if the current route has the given name and parameter values.
   *
   * @param string $route_name
   *   The route name to match.
   * @param array $route_parameters
   *   (optional) An associative array of parameter names and values to match,
   *   like the second parameter of Drupal\Core\Url::fromRoute().
   *
   * @return bool
   *   TRUE if the current route matches the given route name and parameter
   *   values.
   */
  public function matches($route_name, array $route_parameters = []) {
    // If no parameters are specified, just check the route name.
    if (!$route_parameters) {
      return $this->routeNameMatches($route_name);
    }
    // If parameters are specified, check the route name, then the parameters.
    if ($this->routeNameMatches($route_name)) {
      return $this->parametersMatch($route_parameters);
    }
    // No parameters specified, route name doesn't match, no match.
    return FALSE;
  }

  /**
   * Return TRUE if the route name matches the current route name.
   *
   * @param string $route_name
   *   The route name to match.
   *
   * @return bool
   *   TRUE if the route name matches the current route name.
   */
  public function routeNameMatches($route_name) {
    return $this->currentRouteMatch->getRouteName() == $route_name;
  }

  /**
   * Return TRUE if all parameters have a matching value in the current route.
   *
   * @param array $route_parameters
   *   An associative array of parameter names and values.
   *
   * @return bool
   *   TRUE if all given parameters have a matching value in the current route.
   */
  public function parametersMatch(array $route_parameters) {
    foreach ($route_parameters as $parameter_name => $value) {
      if (!$this->parameterMatches($parameter_name, $value)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Return TRUE if the current route has a matching value for the given name.
   *
   * @param string $parameter_name
   *   The parameter to match. If the current route has no value for this
   *   parameter, it will be considered not matching.
   * @param mixed $value
   *   If the value is a string, it will be compared to the result of calling
   *   id() on the current route's value for the parameter. If not, this
   *   function will check if the value has a method id(), then compare that.
   *   Otherwise the value will be considered not matching.
   *
   * @return bool
   *   TRUE if the current route has a matching value for the given name.
   */
  public function parameterMatches($parameter_name, $value) {
    if ($current_value = $this->getParameter($parameter_name)) {
      if (is_string($value)) {
        return $current_value->id() == $value;
      }
      elseif (method_exists($value, 'id')) {
        return $current_value->id() == $value->id();
      }
      else {
        return FALSE;
      }
    }
    return FALSE;
  }

  /**
   * Return a parameter from the current route.
   *
   * @param string $parameter
   *   The parameter name.
   *
   * @return mixed|null
   *   The parameter value. NULL if the route doesn't define the parameter or
   *   if the parameter value can't be determined from the request.
   */
  public function getParameter($parameter) {
    return $this->currentRouteMatch->getParameter($parameter);
  }

  /**
   * Return TRUE if the current route matches the listing view page route.
   *
   * @return bool
   *   TRUE if the current route matches the listing view page route.
   */
  public function isListingsPage() {
    return $this->routeNameMatches(NJBRoute::LISTINGS);
  }

  /**
   * Return TRUE if the current route matches the register page route.
   *
   * @param mixed $user_role
   *   (optional) A user role object, or the machine name of a user role.
   *
   * @return bool
   *   TRUE if the current route matches the register page route.
   */
  public function isRegisterPage($user_role = NULL) {
    $parameters = $user_role ? ['user_role' => $user_role] : [];
    return $this->matches(NJBRoute::REGISTER, $parameters);
  }

  /**
   * Return TRUE if the current route matches the login page route.
   *
   * @return bool
   *   TRUE if the current route matches the login page route.
   */
  public function isLoginPage() {
    return $this->matches(NJBRoute::LOGIN);
  }

  /**
   * Return TRUE if the current route matches route of the given entity.
   *
   * @param Drupal\Core\Entity\EntityInterface|string $entity
   *   The entity object or entity id to match.
   * @param string $entity_type
   *   The entity type of the entity.
   * @param string $sub_route
   *   (optional) The sub_route to check, e.g. "edit_form". Defaults to
   *   "canonical".
   *
   * @return bool
   *   TRUE if the current route matches the entity and sub-route.
   */
  public function isEntity($entity, $entity_type, $sub_route = 'canonical') {
    $entity_route = "entity.$entity_type.$sub_route";
    $parameters[$entity_type] = $entity;
    return $this->matches($entity_route, $parameters);
  }

  /**
   * Return TRUE if the current route matches the given node's route.
   *
   * @param Drupal\node\NodeInterface|string $node
   *   The node object or node id to match.
   * @param string $sub_route
   *   The sub-route to match, e.g. 'edit_form'. Defaults to 'canonical'.
   *
   * @return bool
   *   TRUE if the current route matches the given node and sub-route.
   */
  public function isNode($node, $sub_route = 'canonical') {
    return $this->isEntity($node, 'node', $sub_route);
  }

  /**
   * Return TRUE if the current route matches the given user's route.
   *
   * @param Drupal\user\UserInterface|string $user
   *   The user object or user id to match.
   * @param string $sub_route
   *   The sub-route to match, e.g. 'edit_form'. Defaults to 'canonical'.
   *
   * @return bool
   *   TRUE if the current route matches the given user and sub-route.
   */
  public function isUser($user, $sub_route = 'canonical') {
    return $this->isEntity($user, 'user', $sub_route);
  }

  /**
   * Return TRUE if the current route matches the current user's route.
   *
   * @param string $sub_route
   *   The sub-route to match, e.g. 'edit_form'. Defaults to 'canonical'.
   *
   * @return bool
   *   TRUE if the current route matches the current user and sub-route.
   */
  public function isCurrentUser($sub_route = 'canonical') {
    return $this->isUser($this->currentUser, $sub_route);
  }

  /**
   * Return TRUE if the current route matches the given node type's add form.
   *
   * @param string $node_type
   *   The machine name of a node type.
   *
   * @return bool
   *   TRUE if the current route matches the given node type's add form.
   */
  public function isNodeAdd($node_type) {
    return $this->matches('node.add', ['node_type' => $node_type]);
  }

}

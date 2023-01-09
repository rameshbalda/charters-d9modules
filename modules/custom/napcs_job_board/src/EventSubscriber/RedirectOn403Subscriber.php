<?php

namespace Drupal\napcs_job_board\EventSubscriber;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\napcs_job_board\NJBRouteHelper;
use Drupal\napcs_job_board\NJBRoute;

/**
 * Class to handle 403 exceptions.
 */
class RedirectOn403Subscriber extends HttpExceptionSubscriberBase {

  protected $currentUser;
  protected $routeHelper;
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountInterface $current_user, NJBRouteHelper $route_helper) {
    $this->currentUser = $current_user;
    $this->routeHelper = $route_helper;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return ['html'];
  }

  /**
   * {@inheritdoc}
   */
  public function on403(GetResponseForExceptionEvent $event) {
    $is_anonymous = $this->currentUser->isAnonymous();
    $is_add_application = $this->routeHelper->isNodeAdd('application');
    if ($is_anonymous && $is_add_application) {
      $query['destination'] = $this->getDestination($event->getRequest());
      // $query['_type'] = 'jb_fail';.
      $login_uri = Url::fromRoute(NJBRoute::REGISTER, ['user_role' => 'job_seeker'], ['query' => $query])->toString();
      $returnResponse = new RedirectResponse($login_uri);
      \Drupal::messenger()->addWarning(t('Create a Job Seeker account to apply for this job.'));
      $event->setResponse($returnResponse);
    }
  }

  /**
   * Build destination string to return to application form.
   */
  private function getDestination($request) {
    $destination = $request->getPathInfo();
    if ($queryString = $request->getQueryString()) {
      $destination .= '?' . $queryString;
    }
    return $destination;
  }

}

<?php

namespace Drupal\napcs_job_board\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller class for job board user register page.
 */
class UserController implements ContainerInjectionInterface {

  /**
   * The current user service.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity form builder service.
   *
   * @var Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * The form builder service.
   *
   * @var Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Construct the user register controller.
   */
  public function __construct(AccountProxyInterface $account, EntityTypeManagerInterface $entity_type_manager, EntityFormBuilderInterface $entity_form_builder, FormBuilderInterface $form_builder) {
    $this->currentUser = $account;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFormBuilder = $entity_form_builder;
    $this->formBuilder = $form_builder;
  }

  /**
   * Return an instance of the user register controller.
   */
  public static function create(ContainerInterface $container) {
    // Inject the form builder and current user.
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('entity.form_builder'),
      $container->get('form_builder')
    );
  }

  /**
   * Title callback for user registration page.
   */
  public function registerTitle(RoleInterface $user_role = NULL) {
    if ($user_role) {
      // Use role injected from route to build title.
      return t('Create @role account', ['@role' => $user_role->label()]);
    }
    else {
      return 'Register for the Job Board';
    }
  }

  /**
   * Return the user register form.
   */
  public function register(RoleInterface $user_role = NULL) {
    if ($this->userIsAuthenticated()) {
      return $this->userRedirect();
    }
    return $this->entityFormBuilder->getForm($this->newUser(), 'register');
  }

  /**
   * Return the user login form.
   */
  public function login() {
    if ($this->userIsAuthenticated()) {
      return $this->userRedirect();
    }
    return $this->formBuilder->getForm('Drupal\user\Form\UserLoginForm');
  }

  /**
   * Return the user password form.
   */
  public function password() {
    return $this->formBuilder->getForm('Drupal\user\Form\UserPasswordForm');
  }

  /**
   * Return a new user entity object.
   */
  protected function newUser() {
    return $this->entityTypeManager->getStorage('user')->create();
  }

  /**
   * Return TRUE if the current user is logged in.
   */
  protected function userIsAuthenticated() {
    return $this->currentUser->isAuthenticated();
  }

  /**
   * Return a redirect response to the current user's account page.
   */
  protected function userRedirect() {
    $user = User::load($this->currentUser->id());
    return RedirectResponse::create($user->toUrl()->toString());
  }

}

<?php

namespace Drupal\napcs_job_board;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Job board user helper.
 */
class NJBUserHelper {

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * The entity type manager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * An array of machine names of job board roles.
   *
   * @var string[]
   */
  protected $jobBoardRoles = [
    'employer',
    'job_seeker',
  ];

  /**
   * An array of roles allowed to bypass admin approval.
   *
   * @var string[]
   */
  protected $bypassRoles = ['job_seeker'];

  /**
   * An array of arrays of fields, indexed by permissions.
   *
   * @var array
   */
  protected $userPermissionFields = [
    'create profile content' => [
      'field_profile',
    ],
    'administer users' => [
      'field_first_name',
      'field_last_name',
      'field_org_name',
      'field_website',
    ],
  ];

  /**
   * Constructs a new NJBUserHelper object.
   */
  public function __construct(AccountProxy $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Return an array of job board role machine names.
   *
   * @return string[]
   *   An array of job board role machine names.
   */
  public function getJobBoardRoles() {
    return $this->jobBoardRoles;
  }

  /**
   * Return an array of job board role machine names.
   *
   * @return string[]
   *   An array of job board role machine names.
   */
  public function getBypassRoles() {
    return $this->bypassRoles;
  }

  /**
   * Return a boolean indicating whether a user role can bypass admin approval.
   *
   * @param Drupal\user\RoleInterface|string $user_role
   *   A user role object or machine name.
   *
   * @return bool
   *   TRUE if the role can bypass approval.
   */
  public function isJobBoardRole($user_role) {
    if (!is_string($user_role)) {
      $user_role = $user_role->id();
    }
    return in_array($user_role, $this->getJobBoardRoles());
  }

  /**
   * Return a boolean indicating whether a user role can bypass admin approval.
   *
   * @param Drupal\user\RoleInterface|string $user_role
   *   A user role object or machine name.
   *
   * @return bool
   *   TRUE if the role can bypass approval.
   */
  public function isBypassRole($user_role) {
    if ($this->isJobBoardRole($user_role)) {
      if (!is_string($user_role)) {
        $user_role = $user_role->id();
      }
      return in_array($user_role, $this->getBypassRoles());
    }
    throw new \InvalidArgumentException($user_role . 'is not a valid job board role');
  }

  /**
   * Return TRUE if the user has the employer role.
   *
   * @param Drupal\user\UserInterface $user
   *   (optional) The user object. If no value is supplied, use the current
   *   user.
   *
   * @return bool
   *   TRUE if the user has the employer role.
   */
  public function isEmployer(UserInterface $user = NULL) {
    if (!$user) {
      $user = $this->getCurrentUser();
    }
    return $user->hasRole('employer');
  }

  /**
   * Return a link to a job board register page.
   *
   * @param string $user_role
   *   The machine name of a job board role.
   *
   * @return Drupal\Core\Link
   *   A link to a register page.
   */
  public function getRegisterLink($user_role) {
    if ($this->isJobBoardRole($user_role)) {
      $role = Role::load($user_role);
      $text = t('Register as @role', ['@role' => $role->label()]);
      $link = Link::createFromRoute($text, NJBRoute::REGISTER, ['user_role' => $user_role]);
      return $link;
    }
    throw new \InvalidArgumentException($user_role . 'is not a valid job board role');
  }

  /**
   * Alter the user edit form.
   *
   * @param array $form
   *   The form render array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @see napcs_job_board_form_user_form_alter()
   */
  public function userFormAlter(array &$form, FormStateInterface $form_state) {
    if ($form['#form_id'] == 'user_form') {
      $this->setFieldAccessByPermission($form);
    }
  }

  /**
   * Alter the user view build.
   *
   * @see napcs_job_board_user_view_alter()
   */
  public function userViewAlter(&$build, $user, $display) {
    $this->setFieldAccessByPermission($build);
  }

  /**
   * Set access for elements in a render array.
   *
   * @param array $build
   *   A render array.
   *
   * @see $userPermissionFields
   */
  protected function setFieldAccessByPermission(array &$build) {
    foreach ($this->userPermissionFields as $permission => $fields) {
      foreach ($fields as $field) {
        if (isset($build[$field])) {
          $build[$field]['#access'] = $this->getCurrentUser()->hasPermission($permission);
        }
      }
    }
  }

  /**
   * Return TRUE if user is the author of any organization content.
   *
   * @param Drupal\user\UserInterface $user
   *   The user.
   *
   * @return bool
   *   TRUE if the user is the author of any organization content.
   */
  public function getOrganizations(UserInterface $user) {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $organizations = $node_storage->loadByProperties([
      'type' => 'organization',
      'uid' => $user->id(),
    ]);
    return $organizations;
  }

  /**
   * Gets the current User's id.
   *
   * @return int
   *   The current user's id.
   */
  public function currentUserId() {
    return $this->currentUser->id();
  }

  /**
   * Loads the current user.
   *
   * @return \Drupal\user\Entity\User
   *   The current user object.
   */
  public function getCurrentUser() {
    return User::load($this->currentUserId());
  }

  /**
   * Retrieves a User's Profile.
   *
   * @param null|\Drupal\user\Entity\User $user
   *   An object that is supposed
   *         to be an instance of user or null.
   * @param int $id
   *   The id from which we want to load a user.
   * @param bool $currentUser
   *   Whether or not to just load from the current user.
   *
   * @return \Drupal\node\Entity\Node|false
   *   False if not a valid user object or user id.
   *   Node of type profile if valid and found.
   */
  public function getUserProfile($user = NULL, $id = NULL, $currentUser = FALSE) {
    $validUser = !empty($user) && $user instanceof User;
    if (!$validUser && is_numeric($id)) {
      $user = User::load($id);
    }
    elseif ($currentUser) {
      $user = $this->getCurrentUser();
    }
    $profile_entities = $user->get('field_profile')->referencedEntities();
    return reset($profile_entities);
  }

  /**
   * Retrieves a file resume from a user's profile.
   *
   * @param null|\Drupal\node\Entity\Node $profile
   *   An object that is supposed to be a node and an instance of Node.
   * @param int $profile_id
   *   The id from which we want to load a profile.
   * @param bool $currentUser
   *   Whether or not to just load from the current user.
   *
   * @return bool|File
   *   False if not found or not valid profile,
   *   File if found and valid profile.
   */
  public function getUserProfileResume($profile = NULL, $profile_id = NULL, $currentUser = FALSE) {
    $validProfile = $this->validProfile($profile);
    $validId = is_numeric($profile_id);
    if (!$validProfile && $validID) {
      $profile = Node::load($profile_id);
    }
    elseif ($currentUser) {
      $profile = $this->getUserProfile(NULL, NULL, TRUE);
    }

    if ($validProfile) {
      return $this->getResume($profile);
    }

    return FALSE;
  }

  /**
   * Get's a Resume attachement from a profile content type.
   *
   * @param \Drupal\node\Entity\Node $profile
   *   A Node object that should be content type profile.
   *
   * @return \Drupal\file\Entity\File
   *   A resume file attachement.
   */
  private function getResume(Node $profile) {
    if ($resume_id = $profile->field_resume->target_id) {
      return File::load($resume_id);
    }
    return NULL;
  }

  /**
   * Check if a variable is not empty and is an insance of Node.
   *
   * @param null|\Drupal\node\Entity\Node $profile
   *   An object that is supposed to be a node and an instance of Node.
   *
   * @return bool
   *   True if the variable is not empty and is an instance of Node.
   */
  private function validProfile($profile = NULL) {
    return !empty($profile) && $profile instanceof Node;
  }

  /**
   * Appends a value from a drupal Node interface to text.
   *
   * @param \Drupal\node\Entity\Node $node
   *   A drupal node interface.
   * @param string $field
   *   The field from which we want to retrieve the value.
   * @param string &$text
   *   The text to which we want to append a value.
   */
  private function appendVal(Node $node, $field, &$text) {
    if ($node->hasField($field)) {
      $value = $node->get($field)->getValue();
      if (!empty($value)) {
        $text .= $value[0]['value'];
      }
    }
  }

}

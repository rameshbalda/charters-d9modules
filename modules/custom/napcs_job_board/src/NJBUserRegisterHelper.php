<?php

namespace Drupal\napcs_job_board;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\node\Entity\Node;

/**
 * Contains the NAPCS Job Board user registration helper service.
 */
class NJBUserRegisterHelper {

  /**
   * The route helper service.
   *
   * @var Drupal\napcs_job_board\NJBRouteHelper
   */
  protected $routeHelper;

  /**
   * An array of arrays of user field names indexed by role.
   *
   * @var array
   * @see formAlter()
   */
  protected $userFields = [
    'job_seeker' => [
      'field_first_name' => 'value',
      'field_last_name' => 'value',
    ],
    'employer' => [
      'field_org_name' => 'value',
      'field_website' => 'uri',
    ],
  ];

  /**
   * An array of field mapping data.
   *
   * @var array
   * @see userInsert()
   */
  protected $fieldsMap = [
    // Each role has an array describing how to use the user fields to create
    // nodes.
    'job_seeker' => [
      // The type of node to create.
      'type' => 'profile',
      // Copy values from $source => $target.
      'fields' => [
        'field_first_name' => 'field_first_name',
        'field_last_name' => 'field_last_name',
      ],
    ],
    'employer' => [
      'type' => 'organization',
      'fields' => [
        'field_org_name' => 'title',
        'field_website' => 'field_website',
      ],
    ],
  ];

  /**
   * Object constructor.
   */
  public function __construct(NJBRouteHelper $route_helper, NJBUserHelper $user_helper) {
    $this->routeHelper = $route_helper;
    $this->userHelper = $user_helper;
  }

  /**
   * Alter the user register form for custom job board behavior.
   *
   * @param array $form
   *   The user register form.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The user register form form state object.
   *
   * @see Drupal\user\RegisterForm
   * @see Drupal\napcs_job_board\Controller\UserRegisterController
   * @see napcs_job_board_form_user_register_form_alter()
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
    // Quit if not on the custom register form page...
    if (!$this->routeHelper->isRegisterPage()) {
      return;
    }

    // Give the user the role referenced in the route.
    $user_role = $this->getRoleFromRoute();
    $form['account']['roles'][$user_role->id()]['#default_value'] = TRUE;
    // Make user active if role is allowed to bypass approval.
    if ($this->userHelper->isBypassRole($user_role)) {
      $form['account']['status']['#default_value'] = 1;
    }
    // Require or hide user fields as appropriate.
    foreach ($this->userFields as $field_role => $field_names) {
      if ($user_role->id() == $field_role) {
        foreach ($field_names as $field_name => $value_property) {
          foreach (Element::children($form[$field_name]['widget']) as $delta) {
            $form[$field_name]['widget'][$delta][$value_property]['#required'] = TRUE;
          }
        }
      }
      else {
        foreach ($field_names as $field_name => $value_property) {
          $form[$field_name]['#access'] = FALSE;
        }
      }
    }

    // Add email opt-in checkbox and adjust weight.
    $form['job_board_opt_in'] = [
      '#type' => 'checkbox',
      '#title' => 'Opt-in to updates and emails from the National Alliance for Public Charter Schools',
      '#weight' => max(array_column($form, '#weight')) + 1,
    ];
    $form['actions']['#weight'] = $form['job_board_opt_in']['#weight'] + 1;
    $form['#attached']['library'][] = 'napcs_hubspot/tracking';

    // Add the submit handler which redirects to the job board landing page
    // after registration.
    $form['actions']['submit']['#submit'][] = [$this, 'formRedirect'];
  }

  /**
   * Get the user role parameter from the current route.
   *
   * @return Drupal\user\RoleInterface
   *   The user role from the current route.
   */
  private function getRoleFromRoute() {
    return $this->routeHelper->getParameter('user_role');
  }

  /**
   * User registration form submit handler.
   */
  public function formRedirect($form, FormStateInterface $form_state) {
    // Redirect to the job board listings page.
    $form_state->setRedirect('view.napcs_job_listings.page_1');
  }

  /**
   * Respond to new user creation.
   */
  public function userInsert($account) {
    // If this wasn't the job board register page, quit.
    if (!$this->routeHelper->isRegisterPage()) {
      return;
    }
    // Create the appropriate content item for the role of the new user.
    foreach ($account->getRoles() as $role) {
      $field_map = $this->getFieldsToCopy($role);
      if (!$field_map) {
        continue;
      }
      $node_data = [
        'uid' => $account->id(),
        'type' => $field_map['type'],
      ];
      foreach ($field_map['fields'] as $source => $target) {
        $node_data[$target] = $account->get($source)->getValue();
      }
      $node = Node::create($node_data);
      $node->save();
    }
  }

  /**
   * Return field info for copying fields from users to their content.
   */
  protected function getFieldsToCopy($role) {
    return $this->fieldsMap[$role] ?? false;
  }

}

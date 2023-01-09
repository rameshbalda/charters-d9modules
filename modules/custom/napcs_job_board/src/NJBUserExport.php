<?php

namespace Drupal\napcs_job_board;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Drupal\user\UserInterface;
use League\Csv\Writer;

/**
 * Class NJBUserExport.
 *
 * Meant to be invoked from a script via `drush eval`. Put a call to this
 * class's `export` function in something like "user-export.php" in the webroot,
 * then `include` it with `drush eval` and there will be a file
 * "napcs_job_board_user_export.csv" created in that directory.
 *
 * @code
 * // user_export.php
 * <?php
 * /Drupal::service('napcs_job_board.user_export')->export();
 *
 * // Then run:
 * $ drush eval 'include "user_export.php";'
 * @endcode
 */
class NJBUserExport {

  /**
   * The user storage object.
   *
   * @var Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * The user migration id map.
   *
   * @var Drupal\migrate\Plugin\MigrateIdMapInterface
   */
  protected $userMap;

  /**
   * The CSV writer object.
   *
   * @var League\Csv\Writer
   */
  protected $csv;

  /**
   * An array of column names.
   *
   * For each value this class will search for a function of the same name which
   * accepts a UserInterface as an argument and returns that user's value for
   * the column.
   *
   * @var string[]
   */
  protected $columnNames = [
    'drupal_id',
    'ID',
    'user_login',
    'user_nicename',
    'first_name',
    'last_name',
    'is_employer',
  ];

  /**
   * Constructs a new NJBUserExport object.
   */
  public function __construct(EntityTypeManager $entity_type_manager, MigrationPluginManager $plugin_manager_migration) {
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->userMap = $plugin_manager_migration->createInstance('napcs_job_board_user')->getIdMap();
    $this->csv = Writer::createFromString(implode(',', $this->columnNames));
  }

  /**
   * Write a user export csv to disk.
   */
  public function export() {
    array_map([$this, 'addUserRow'], $this->getUsers());
    file_put_contents('napcs_job_board_user_export.csv', (string) $this->csv);
  }

  /**
   * Return an array of users with role 'job_seeker' or 'employer'.
   *
   * @return Drupal\user\UserInterface[]
   *   An array of user objects.
   */
  protected function getUsers() {
    return $this->userStorage->loadByProperties(['roles' => ['job_seeker', 'employer']]);
  }

  /**
   * Add a row of user data to the CSV.
   *
   * @param Drupal\user\UserInterface $user
   *   A user object.
   */
  protected function addUserRow(UserInterface $user) {
    $user_row = array_reduce($this->columnNames, function ($user_row, $field_name) use ($user) {
      $user_row[$field_name] = $this->getFieldValue($user, $field_name);
      return $user_row;
    }, []);
    $this->csv->insertOne($user_row);
  }

  /**
   * Get a value for an export field.
   *
   * @param Drupal\user\UserInterface $user
   *   The user object for which to get a value.
   * @param string $field_name
   *   The field name. Will be tested to see if this class has a function of
   *   that name.
   *
   * @return mixed
   *   The field value, usually a string, or NULL if there is no function for
   *   that field.
   */
  protected function getFieldValue(UserInterface $user, $field_name) {
    return method_exists($this, $field_name) ? $this->{$field_name}($user) : NULL;
  }

  /**
   * Value function for drupal_id.
   *
   * @param Drupal\user\UserInterface $user
   *   The user object for which to get the value.
   *
   * @return string
   *   The user's uid.
   */
  protected function drupal_id(UserInterface $user) {
    return $user->id();
  }

  /**
   * Value function for ID.
   *
   * @param Drupal\user\UserInterface $user
   *   The user object for which to get the value.
   *
   * @return string
   *   The user's source id from the Wordpress migration.
   */
  protected function ID(UserInterface $user) {
    return $this->userMap->lookupSourceID(['uid' => $user->id()])['id'];
  }

  /**
   * Value function for user_login.
   *
   * @param Drupal\user\UserInterface $user
   *   The user object for which to get the value.
   *
   * @return string
   *   The user's login name.
   */
  protected function user_login(UserInterface $user) {
    return $user->getAccountName();
  }

  /**
   * Value function for user_nicename.
   *
   * @param Drupal\user\UserInterface $user
   *   The user object for which to get the value.
   *
   * @return string
   *   The user's login name.
   */
  protected function user_nicename(UserInterface $user) {
    return $this->user_login($user);
  }

  /**
   * Value function for first_name.
   *
   * @param Drupal\user\UserInterface $user
   *   The user object for which to get the value.
   *
   * @return string
   *   The first value of field_first_name from the user's profile.
   */
  protected function first_name(UserInterface $user) {
    return $this->getProfileValue($user, 'field_' . __FUNCTION__);
  }

  /**
   * Value function for last_name.
   *
   * @param Drupal\user\UserInterface $user
   *   The user object for which to get the value.
   *
   * @return string
   *   The first value of field_last_name from the user's profile.
   */
  protected function last_name(UserInterface $user) {
    return $this->getProfileValue($user, 'field_' . __FUNCTION__);
  }

  /**
   * Value function for is_employer.
   *
   * @param Drupal\user\UserInterface $user
   *   The user object for which to get the value.
   *
   * @return string
   *   "1" if the user has the employer role, or "NULL".
   */
  protected function is_employer(UserInterface $user) {
    return $user->hasRole('employer') ?: 'NULL';
  }

  /**
   * Get the first value of a field from a user's profile.
   *
   * @param Drupal\user\UserInterface $user
   *   The user whose profile has the value.
   * @param string $field_name
   *   The field to get a value for.
   *
   * @return string
   *   The field's value, or NULL if none was returned.
   */
  protected function getProfileValue(UserInterface $user, $field_name) {
    $profile = $this->getProfile($user);
    if ($profile) {
      return $this->entityFieldValue($profile, $field);
    }
    return NULL;
  }

  /**
   * Get a user's profile entity.
   *
   * @param Drupal\user\UserInterface $user
   *   The user whose profile to get.
   *
   * @return Drupal\node\NodeInterface
   *   The user's profile node.
   */
  protected function getProfile(UserInterface $user) {
    $profiles = $user->get('field_profile')->referencedEntities();
    return reset($profiles);
  }

  /**
   * Get an entity's first value for a field.
   *
   * @param Drupal\Core\Entity\EntityInterface $entity
   *   The entity to get the value from.
   * @param string $field_name
   *   The field to get the value from.
   * @param string $column
   *   The name of the property of the field which holds the value.
   *
   * @return string
   *   The field value from the entity, or NULL if no value is found.
   */
  protected function entityFieldValue(EntityInterface $entity, $field_name, $column = 'value') {
    if ($entity->hasField($field_name) && $item = $entity->get($field_name)->first()) {
      $value = $item->getValue();
      if (isset($value[$column])) {
        return $value[$column];
      }
      throw new \InvalidArgumentException("Value from $field_name does not have a property '$column'");
    }
    return NULL;
  }

}

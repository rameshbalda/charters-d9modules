<?php

namespace Drupal\napcs_job_board;

use Drupal\user\Entity\User;

/**
 * Job Board access helper class.
 */
class NJBAccessHelpers {

  /**
   * Loads the Interface for the user.
   *
   * @param int $uid
   *   The user id.
   *
   * @return Drupal\user\UserInterface
   *   A User object.
   */
  public static function load(int $uid) {
    return User::load($uid);
  }

  /**
   * Checks if a user has a field.
   *
   * @param int $uid
   *   User ID.
   * @param string $field
   *   Key for the field.
   * @param null|UserInterface $user
   *   empty or an instance of the drupal UserInterface.
   *
   * @return bool
   *   True if user has given field.
   */
  public static function hasField(int $uid, $field = '', $user = NULL) {
    if ($user === NULL) {
      $user = self::load($uid);
    }

    return $user->hasField($field);
  }

  /**
   * Gets a field assigned to a user.
   *
   * @param int $uid
   *   User ID.
   * @param string $field
   *   Key for the field.
   * @param null|UserInterface $user
   *   empty or an instance of the drupal UserInterface.
   *
   * @return mixed
   *   Usually an array of values.
   */
  public static function getField(int $uid, $field = '', $user = NULL) {
    if ($user === NULL) {
      $user = self::load($uid);
    }

    $value = NULL;
    if (self::hasField(0, $field, $user)) {
      $value = $user->get($field);
      if (!empty($value)) {
        $value = $value->getValue();
      }
      return $value;
    }
  }

  /**
   * Gets the first value's target id.
   *
   * @param int $uid
   *   User ID.
   * @param string $field
   *   Key for the field.
   *
   * @return mixed
   *   Usually a string. Maybe an int/
   */
  public static function getFirstTarget(int $uid, $field) {
    $value = self::getField($uid, $field);
    if (is_array($value) && isset($value[0]['target_id'])) {
      return $value[0]['target_id'];
    }

    return $value;
  }

}

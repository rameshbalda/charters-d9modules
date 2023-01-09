<?php

namespace Drupal\napcs_job_board\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Source plugin for Users.
 *
 * @MigrateSource(
 *   id = "job_board_user"
 * )
 */
class User extends JobBoardSourceBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->userQuery([
      'id',
      'user_login',
      'user_email',
      'user_registered',
      'user_status',
    ]);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => $this->t('User ID'),
      'user_login' => $this->t('User name'),
      'user_email' => $this->t('Email'),
      'user_registered' => $this->t('Registration date'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'integer',
        'alias' => 'u',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Add role data based on what content ids are present for this user.
    if ($roles = $this->getUserRoles($row)) {
      $row->setSourceProperty('roles', $roles);
    }
    else {
      // If no roles are found, skip this row, because we only care about users
      // that are job seekers and/or employers.
      return FALSE;
    }
  }

  /**
   * Return array of roles for a given row.
   */
  protected function getUserRoles(Row $row) {
    $roles = [];
    $user_id = $row->getSourceProperty('id');
    foreach ($this->roleIds as $role => $role_ids) {
      if (in_array($user_id, $role_ids)) {
        $roles[] = $role;
      }
    }
    return $roles;
  }

}

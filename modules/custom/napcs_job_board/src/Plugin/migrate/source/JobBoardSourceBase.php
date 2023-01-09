<?php

namespace Drupal\napcs_job_board\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Provide base queries for NAPCS Job Board migrations.
 */
abstract class JobBoardSourceBase extends SqlBase {

  /**
   * Maps content id columns to role names.
   *
   * @var array
   */
  protected $roleTables = [
    'job_seeker' => 'wpjb_application',
    'employer' => 'wpjb_employer',
  ];

  /**
   * Maps role names to arrays of user_ids.
   *
   * @var array
   */
  protected $roleIds = [];

  /**
   * Return a query selecting users with application or employer content.
   */
  protected function userQuery(array $fields) {
    $query = $this->select('wpnap_users', 'u')
      ->fields('u', $fields);
    // Get users that have application or employer content.
    $has_content = $query->orConditionGroup();
    foreach ($this->roleTables as $role => $table) {
      $subquery = $this->distinctQuery($table, $role, 'user_id');
      $has_content->condition('u.id', $subquery, 'IN');
      // Store results of subqueries for assigning roles in prepareRow.
      $this->roleIds[$role] = $subquery->execute()->fetchCol();
    }
    $query->condition($has_content);
    return $query;
  }

  /**
   * Return a query selecting the distinct values from a table.
   */
  protected function distinctQuery($table, $alias, $column) {
    $user_id_query = $this->select($table, $alias)
      ->fields($alias, [$column])
      ->distinct();
    return $user_id_query;
  }

  /**
   * Get employer (organization) data for migrated users.
   */
  protected function organizationQuery(array $fields) {
    $user_query = $this->userQuery(['id']);
    $query = $this->select('wpjb_employer', 'e')
      ->fields('e', $fields)
      ->isNotNull('e.company_name')
      ->condition('e.company_name', '', '!=')
      ->condition('user_id', $user_query, 'IN');
    return $query;
  }

  /**
   * Get profile data for migrated users.
   */
  protected function profileQuery(array $fields) {
    $user_query = $this->distinctQuery('wpjb_application', 'a', 'user_id');
    $query = $this->select('wpnap_users', 'u')
      ->fields('u', $fields)
      ->condition('u.id', $user_query, 'IN');
    return $query;
  }

  /**
   * Get job listings from the last 3 months by migrated organizations.
   */
  protected function listingQuery(array $fields) {
    $months = 3;
    $timeframe = date('Y-m-d', strtotime("$months months ago"));
    $employers_query = $this->organizationQuery(['id']);
    $query = $this->select('wpjb_job', 'j')
      ->fields('j', $fields)
      ->condition('j.job_created_at', $timeframe, '>')
      ->condition('j.employer_id', $employers_query, 'IN');
    return $query;
  }

  /**
   * Get applications for migrated job listings.
   */
  protected function applicationQuery(array $fields) {
    $listing_query = $this->listingQuery(['id']);
    $query = $this->select('wpjb_application', 'a')
      ->fields('a', $fields)
      ->condition('job_id', $listing_query, 'IN');
    return $query;
  }

}

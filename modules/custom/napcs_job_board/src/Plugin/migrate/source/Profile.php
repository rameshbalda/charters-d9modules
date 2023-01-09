<?php

namespace Drupal\napcs_job_board\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Source plugin for Profiles.
 *
 * @MigrateSource(
 *   id = "job_board_profile"
 * )
 */
class Profile extends JobBoardSourceBase {

  /**
   * User_meta keys to attach to each row.
   *
   * @var array
   */
  protected $metaKeys = [
    'description',
    'first_name',
    'last_name',
    'twitter',
  ];

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->profileQuery([
      'id',
      'user_login',
      'user_email',
      'user_registered',
      'user_url',
    ]);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => $this->t('User ID'),
      'user_url' => $this->t('Website'),
      'first_name' => t('First name'),
      'last_name' => t('Last name'),
      'twitter' => t('Twitter ID'),
      'description' => t('Description'),

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
    $user_id = $row->getSourceProperty('id');
    // Get user metadata (names, bio, twitter).
    $meta_data = $this->getUserMeta($user_id);
    foreach ($meta_data as $meta_datum) {
      $row->setSourceProperty($meta_datum['meta_key'], $meta_datum['meta_value']);
    }
    // If user has name data, we're done.
    if ($row->getSourceProperty('first_name') || $row->getSourceProperty('last_name')) {
    }
    // If user has no name data, attempt to set names via app data.
    elseif ($app_names = $this->getNamesFromApp($user_id)) {
      foreach ($app_names as $key => $name) {
        $row->setSourceProperty($key, $name);
      }
    }
    // If no names can be found, use username as last name.
    else {
      $row->setSourceProperty('last_name', $row->getSourceProperty('user_login'));
    }
  }

  /**
   * Get metadata for a user.
   */
  protected function getUserMeta($user_id) {
    $meta_data = $this->select('wpnap_usermeta', 'm')
      ->fields('m', [
        'meta_key',
        'meta_value',
      ])
      ->condition('user_id', $user_id)
      ->condition('meta_key', $this->metaKeys, 'IN')
      ->isNotNull('meta_value')
      ->condition('meta_value', '', '!=')
      ->execute()
      ->fetchAll();
    return $meta_data;
  }

  /**
   * Set name meta data from name on user's applications.
   */
  protected function getNamesFromApp($user_id) {
    $app_names = [];
    $app_name = $this->getAppName($user_id);
    if ($app_name) {
      $app_words = explode(' ', $app_name);
      $app_names['last_name'] = array_pop($app_words);
      $app_names['first_name'] = implode(' ', $app_words);
    }
    return $app_names;
  }

  /**
   * Get longest name used in the represented user's applications.
   */
  protected function getAppName($user_id) {
    $app_name = '';
    $app_names = $this->select('wpjb_application', 'a')
      ->fields('a', ['applicant_name'])
      ->condition('user_id', $user_id)
      ->execute()
      ->fetchCol();
    $app_name = $this->longestString($app_names);
    return $app_name;
  }

  /**
   * Get longest string from an array of strings.
   */
  protected function longestString($array) {
    $unique = array_unique($array);
    $mapping = array_combine($unique, array_map('strlen', $unique));
    $longest = array_shift(array_keys($mapping, max($mapping)));
    return $longest;
  }

}

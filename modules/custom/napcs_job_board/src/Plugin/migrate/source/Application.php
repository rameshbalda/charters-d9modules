<?php

namespace Drupal\napcs_job_board\Plugin\migrate\source;

/**
 * Source plugin for Applications.
 *
 * @MigrateSource(
 *   id = "job_board_application"
 * )
 */
class Application extends JobBoardSourceBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->applicationQuery([
      'id',
      'job_id',
      'user_id',
      'applied_at',
      'title',
      'resume',
    ]);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => t('ID'),
      'job_id' => t('Job ID'),
      'user_id' => t('User ID'),
      'applied_at' => t('Created'),
      'title' => t('Title'),
      'resume' => t('Cover letter'),
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
        'alias' => 'a',
      ],
    ];
  }

}

<?php

namespace Drupal\napcs_job_board\Plugin\migrate\source;

use Drupal\migrate\Row;

/**
 * Source plugin for Job Listings.
 *
 * @MigrateSource(
 *   id = "job_board_listing"
 * )
 */
class Listing extends JobBoardSourceBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->listingQuery([
      'id',
      'job_type',
      'job_category',
      'job_description',
      'job_country',
      'job_state',
      'job_zip_code',
      'job_location',
      'job_title',
      'job_slug',
      'job_created_at',
      'job_modified_at',
      'is_approved',
      'is_active',
      'is_filled',
      'employer_id',
    ]);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => t('ID'),
      'job_type' => t('Job Type'),
      'job_category' => t('Job Category'),
      'job_description' => t('Job Description'),
      'job_country' => t('Country'),
      'job_state' => t('State'),
      'job_zip_code' => t('Zip Code'),
      'job_location' => t('Location'),
      'job_created_at' => t('Created'),
      'job_modified_at' => t('Changed'),
      'job_title' => t('Title'),
      'job_slug' => t('Slug'),
      'is_approved' => t('Is approved'),
      'is_active' => t('Is active'),
      'is_filled' => t('Is filled'),
      'employer_id' => t('Employer ID'),
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
        'alias' => 'j',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Concatenate location, state and zip to serve as migrated location.
    $location_columns = [
      'job_location',
      'job_state',
      'job_zip',
    ];
    $location_segments = array_filter(array_map(function ($column) use ($row) {
      return $row->getSourceProperty($column);
    }, $location_columns));
    if ($location = implode(', ', $location_segments)) {
      $row->setSourceProperty('location', $location);
    }
  }

}

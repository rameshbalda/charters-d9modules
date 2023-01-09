<?php

namespace Drupal\napcs_job_board\Plugin\migrate\source;

/**
 * Source plugin for Job Types.
 *
 * @MigrateSource(
 *   id = "job_board_type"
 * )
 */
class Type extends TaxonomyBase {

  /**
   * {@inheritdoc}
   */
  protected $table = 'wpjb_type';

}

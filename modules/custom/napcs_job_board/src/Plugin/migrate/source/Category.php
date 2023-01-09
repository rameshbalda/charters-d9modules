<?php

namespace Drupal\napcs_job_board\Plugin\migrate\source;

/**
 * Source plugin for Categories.
 *
 * @MigrateSource(
 *   id = "job_board_category"
 * )
 */
class Category extends TaxonomyBase {

  /**
   * {@inheritdoc}
   */
  protected $table = 'wpjb_category';

}

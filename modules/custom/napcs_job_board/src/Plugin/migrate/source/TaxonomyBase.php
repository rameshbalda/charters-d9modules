<?php

namespace Drupal\napcs_job_board\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Base for job board taxonomy source plugins.
 */
abstract class TaxonomyBase extends SqlBase {

  /**
   * The name of a table to query.
   *
   * @var string
   */
  protected $table;

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select($this->table, 't');
    $query->fields('t', [
      'id',
      'title',
    ]);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => $this->t('ID'),
      'title' => $this->t('Title'),
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
        'alias' => 't',
      ],
    ];
  }

}

<?php

namespace Drupal\napcs_model_law\Plugin\ModelLawDownload;

use Drupal\napcs_model_law\ModelLawDownloadBase;

/**
 * Handle creation of Model Law State exports.
 *
 * @ModelLawDownload(
 *   id = "state_export",
 *   name = "States: Export"
 * )
 */
class StateExport extends ModelLawDownloadBase {

  /**
   * An array mapping column names to fields.
   *
   * @var array
   */
  protected $fields = [
    'Node ID' => 'nid',
    'Name' => 'title',
    'Year Law Passed' => 'field_ml_year_passed',
    '# of schools' => 'field_ml_num_schools',
    '# of students' => 'field_ml_num_students',
    'Intro text' => 'body',
  ];

  /**
   * Return an array of arrays of Model Law State data.
   *
   * @return array
   *   An array of arrays of Model Law State data.
   */
  protected function generate() {
    $fields = $this->fields;
    $nodes = $this->loadNodesByType('ml_state');
    // Map nodes to arrays of data.
    $rows = array_map(function ($node) use ($fields) {
      // Map array of field names to their values.
      return array_map(function ($field) use ($node) {
        return $node->$field->value;
      }, $fields);
    }, $nodes);
    array_unshift($rows, array_keys($fields));
    return $rows;
  }

}

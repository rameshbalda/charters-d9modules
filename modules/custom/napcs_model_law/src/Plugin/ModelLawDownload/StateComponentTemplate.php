<?php

namespace Drupal\napcs_model_law\Plugin\ModelLawDownload;

use Drupal\napcs_model_law\ModelLawDownloadBase;

/**
 * Handle creation of Model Law State Component Score templates.
 *
 * @ModelLawDownload(
 *   id = "state_component_template",
 *   name = "State Component Score: Template"
 * )
 */
class StateComponentTemplate extends ModelLawDownloadBase {

  /**
   * Return a nested array mapping column names to fields, indexed by bundle.
   *
   * @var string
   */
  protected $fields = [
    'Node ID' => 'nid',
    'State ID' => 'field_ml_state.nid',
    'Component ID' => 'field_ml_component.nid',
    'State' => 'field_ml_state.title',
    'Component' => 'field_ml_component.title',
    'Score' => 'field_ml_state_component_score',
    'Text' => 'body',
  ];

  /**
   * Return the path of a temporary file that is a CSV template.
   */
  protected function generate() {
    $states = $this->loadNodesByType('ml_state');
    $components = $this->loadNodesByType('ml_component');
    foreach ($states as $state) {
      foreach ($components as $component) {
        $score_node = $this->loadNodeByProperties([
          'type' => 'ml_state_component_score',
          'field_ml_state' => $state->id(),
          'field_ml_component' => $component->id(),
        ]);
        // var_dump($score_node);
        // Build rows using data from entities to-be-referenced.
        $rows[] = array_map(function ($field) use ($state, $component, $score_node) {
          // If the field name contains a period that means it is a field of
          // a referenced entity.
          if (strpos($field, '.') !== FALSE) {
            $parts = explode('.', $field);
            // This string will correspond to the name of a variable in the
            // "use" expression above.
            $target_entity = ltrim($parts[0], 'field_ml_');
            $field = $parts[1];
            $value = ${$target_entity}->$field->value;
          }
          elseif ($score_node) {
            $value = $score_node->$field->value;
          }
          return $value ?? '';
        }, $this->fields);

      }
    }
    array_unshift($rows, array_keys($this->fields));
    return $rows;
  }

}

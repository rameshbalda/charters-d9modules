<?php

namespace Drupal\napcs_model_law\Plugin\ModelLawDownload;

use Drupal\napcs_model_law\ModelLawDownloadBase;

/**
 * Handle creation of Model Law State Subcomponent Status templates.
 *
 * @ModelLawDownload(
 *   id = "state_subcomponent_template",
 *   name = "State Subcomponent Status: Template"
 * )
 */
class StateSubcomponentTemplate extends ModelLawDownloadBase {

  /**
   * Return an array of arrays of state subcomponent status metadata.
   */
  protected function generate() {
    $states = $this->loadNodesByType('ml_state');
    $subcomponents = $this->getSubcomponents();
    foreach ($states as $state) {
      foreach ($subcomponents as $subcomponent) {
        $rows[] = $this->makeRow($state, $subcomponent);
      }
    }
    array_unshift($rows, array_keys($rows[0]));
    return $rows;
  }

  /**
   * Build an array of state subcomponent metadata.
   */
  protected function makeRow($state, $subcomponent) {
    $status_node = $this->loadNodeByProperties([
      'field_ml_state' => $state->id(),
      'field_ml_component' => $subcomponent['component_id'],
      'field_ml_subcomponent_group_num' => $subcomponent['group_delta'],
      'field_ml_subcomponent_num' => $subcomponent['delta'],
    ]);
    $row = [
      'State ID' => $state->id(),
      'Component ID' => $subcomponent['component_id'],
      'Group delta' => $subcomponent['group_delta'],
      'Subcomponent delta' => $subcomponent['delta'],
      // Print the state name with for the first component's first subcomponent.
      'State' => $subcomponent['number'] == '1A' ? $state->title->value : '',
      'Component' => $subcomponent['component'],
      'Group Title' => $subcomponent['group'],
      'Subcomponent' => $subcomponent['name'],
      'Subcomponent Number' => $subcomponent['number'],
      'Status' => $status_node ? $status_node->field_ml_state_subcomp_status->value : '',
    ];
    return $row;
  }

  /**
   * Get an array of arrays of subcomponent metadata.
   */
  protected function getSubcomponents() {
    // Get all components.
    $components = $this->loadNodesByType('ml_component');
    foreach ($components as $component) {
      // Count subcomponents across groups.
      $subcomponent_index = 0;
      // For each component, loop through its groups.
      $groups = $component->get('field_ml_subcomponent_groups')->referencedEntities();
      foreach ($groups as $group_delta => $group) {
        // For each group, loop for its subcomponents.
        $subcomponents = $group->get('field_ml_subcomponents')->getValue();
        foreach ($subcomponents as $subcomponent_delta => $subcomponent_item) {
          // Create a row from component, group, and subcomponent data.
          $rows[] = $this->makeSubcomponentRow($component, $subcomponent_index, $group_delta, $group, $subcomponent_delta, $subcomponent_item);
          // Increment subcomponent count.
          $subcomponent_index++;
        }
      }
    }
    return $rows;
  }

  /**
   * Return an array of subcomponent metadata.
   */
  protected function makeSubcomponentRow($component, $subcomponent_index, $group_delta, $group, $subcomponent_delta, $subcomponent_item) {
    $subcomponent = [
      'component_id' => $component->id(),
      // Print the component name with the first subcomponent.
      'component' => $this->getParentTitleIfFirst($subcomponent_index, $component, 'title'),
      'group_delta' => $group_delta,
      // Print the group name with the first subcomponent in the group.
      'group' => $this->getParentTitleIfFirst($subcomponent_delta, $group, 'field_ml_group_title'),
      'delta' => $subcomponent_delta,
      'name' => $subcomponent_item['value'],
      'number' => $this->subcomponentNumber($component, $subcomponent_index),
    ];
    return $subcomponent;
  }

  /**
   * Return a subcomponent number, for example "1A".
   */
  protected function subcomponentNumber($component, $subcomponent_index) {
    return $component->field_ml_position->value . chr($subcomponent_index + 65);
  }

  /**
   * Return the value of $title_field of an entity $parent if $index is zero.
   */
  protected function getParentTitleIfFirst($index, $parent, $title_field) {
    $title = '';
    if ($index === 0) {
      $title = $parent->$title_field->value;
    }
    return $title;
  }

}

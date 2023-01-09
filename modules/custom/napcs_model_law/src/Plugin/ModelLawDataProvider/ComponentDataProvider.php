<?php

namespace Drupal\napcs_model_law\Plugin\ModelLawDataProvider;

use Drupal\napcs_model_law\ModelLawDataProviderBase;

/**
 * Provides model law component data.
 *
 * @ModelLawDataProvider(
 *   id="ml_component",
 * )
 */
class ComponentDataProvider extends ModelLawDataProviderBase {

  /**
   * Return an array of panel render arrays.
   */
  public function getPanels() {
    $panels = [];
    foreach ($this->modelLawData->getStates() as $state) {
      $panels[] = $this->buildPanel($state, $this->currentNode);
    }
    return $panels;
  }

  /**
   * Return a panel render array.
   */
  protected function buildPanel($state, $component) {
    $score_node = $this->modelLawData->getScoreNode($state, $component);
    $heading = $this->buildPanelHeading($state, $component, $score_node);
    $body = $this->buildPanelBody($state, $component, $score_node);
    $panel_id = $this->makePanelId($state, $component);
    $panel = [
      '#theme' => 'bootstrap_panel',
      '#attributes' => [
        'id' => $panel_id,
      ],
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#heading' => $heading,
      '#body' => $body,
    ];
    return $panel;
  }

}

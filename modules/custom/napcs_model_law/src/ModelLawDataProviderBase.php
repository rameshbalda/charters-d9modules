<?php

namespace Drupal\napcs_model_law;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Model Law data providers.
 */
abstract class ModelLawDataProviderBase extends PluginBase implements ModelLawDataProviderInterface, ContainerFactoryPluginInterface {

  /**
   * The node represented by the current page.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $currentNode;

  /**
   * The model law data service.
   *
   * @var \Drupal\napcs_model_law\ModelLawData
   */
  protected $modelLawData;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $current_node = $container->get('current_route_match')->getParameter('node');
    $model_law_data = $container->get('napcs_model_law.data');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $current_node,
      $model_law_data
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, NodeInterface $current_node, ModelLawData $model_law_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentNode = $current_node;
    $this->modelLawData = $model_law_data;
  }

  /**
   * Return panel heading content.
   */
  protected function buildPanelHeading($subject, $component, $score_node) {
    $position = $subject->field_ml_position->value;
    $title = $subject->label();
    $weight = $component->field_ml_weight->value;
    if ($score_node) {
      $points = $score_node->field_ml_state_component_score->value;
      $score = $points * $weight;
    }
    else {
      $points = $score = '-';
    }

    $heading = [
      '#theme' => 'model_law_panel_heading',
      '#position' => $position,
      '#title' => $title,
      '#score' => [
        '#theme' => 'model_law_component_score',
        '#points' => $points,
        '#score' => $score,
        '#wt' => $weight,
        '#total' => $weight * 4,
      ],
    ];
    return $heading;
  }

  /**
   * Return panel body content.
   */
  protected function buildPanelBody($state, $component, $score_node) {
    $question = $component->get('field_ml_component_question')->first()->view();
    if ($score_node && $text_field = $score_node->get('body')->first()) {
      $text = $text_field->view();
    }
    else {
      $text = t('No data available.');
    }
    $subcomponent_groups = $this->getSubcomponentGroups($state, $component);
    $body = [
      '#theme' => 'model_law_panel_body',
      '#question' => $question,
      '#text' => $text,
      '#subcomponent_groups' => $subcomponent_groups,
    ];
    return $body;
  }

  /**
   * Return a render array of subcomponents.
   */
  protected function getSubcomponentGroups($state, $component) {
    $groups = $component->get('field_ml_subcomponent_groups')->referencedEntities();
    $component_number = $component->field_ml_position->value;
    $subcomponent_index = 0;
    $subcomponent_groups = [];
    foreach ($groups as $group_delta => $group) {
      $title = $this->modelLawData->getGroupTitle($group);
      $subcomponents = [];
      $subcomponent_items = $group->get('field_ml_subcomponents')->getValue();
      foreach ($subcomponent_items as $subcomponent_delta => $subcomponent_item) {
        $status_node = $this->modelLawData->getStatus($state, $component, $group_delta, $subcomponent_delta);
        if ($status_node) {
          $status = [
            '#type' => 'model_law_status',
            '#status' => $status_node->field_ml_state_subcomp_status->value,
            '#attributes' => [
              'class' => ['model-law-status--data'],
            ],
          ];
          $subcomponents[] = [
            '#theme' => 'model_law_subcomponent',
            '#status' => $status,
            '#number' => $component_number . chr(65 + $subcomponent_index),
            '#subcomponent' => $subcomponent_item['value'],
          ];
        }
        $subcomponent_index++;
      }
      $subcomponent_groups[] = [
        '#theme' => 'model_law_subcomponent_group',
        '#title' => $title,
        '#subcomponents' => $subcomponents,
      ];
    }
    return $subcomponent_groups;
  }

  /**
   * Generate a CSS id string for a state-component pair.
   */
  protected function makePanelId($state, $component) {
    $state_name = strtolower($state->label());
    $component_id = $component->id();
    $panel_id = Html::cleanCssIdentifier("panel-$state_name-$component_id");
    return $panel_id;
  }

}

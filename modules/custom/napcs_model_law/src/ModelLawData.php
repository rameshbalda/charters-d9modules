<?php

namespace Drupal\napcs_model_law;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\State\StateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Provides model law data utility functions.
 */
class ModelLawData {

  const MAX_POINTS = 4;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, StateInterface $state) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->state = $state;
  }

  /**
   * Return the node requested at the current path.
   */
  public function getCurrentNode() {
    $parent = $this->routeMatch->getParameter('node');
    return $parent;
  }

  /**
   * Return an array of states or components, whichever the parent isn't.
   */
  public function getChildren($parent) {
    $children = [];
    if ($parent->getType() == 'ml_state') {
      $children = $this->getComponents();
    }
    elseif ($parent->getType() == 'ml_component') {
      $children = $this->getStates();
    }
    return $children;
  }

  /**
   * Return an array of Model Law States sorted by title.
   */
  public function getComponents() {
    static $components;
    if (!isset($components)) {
      $components = $this->childrenQuery('ml_component', 'field_ml_position');
    }
    return $components;
  }

  /**
   * Return an array of components.
   */
  public function components() {
    return $this->getComponents();
  }

  /**
   * Return an array of Model Law States sorted by title.
   */
  public function getStates() {
    static $states;
    if (!isset($states)) {
      $states = $this->childrenQuery('ml_state', 'title');
    }
    return $states;
  }

  /**
   * Return an array of states.
   */
  public function states() {
    return $this->getStates();
  }

  /**
   * Build, execute, and load nodes from the result of an entity query.
   */
  public function childrenQuery($type, $sort_field) {
    $children_query = $this->nodeQuery($type)
      ->sort($sort_field)
      ->condition('status', NodeInterface::PUBLISHED);
    $children = Node::loadMultiple($children_query->execute());
    return $children;
  }

  /**
   * Return all score nodes referencing a given node.
   */
  public function getScoreNodes($node) {
    $scores = $this->factsQuery($node, 'ml_state_component_score');
    return $scores;
  }

  /**
   * Return all status nodes referencing a given node.
   */
  public function getStatusNodes($node) {
    $statuses = $this->factsQuery($node, 'ml_state_subcomponent_status');
    return $statuses;
  }

  /**
   * Get nodes of a given type referencing a given node as state or component.
   */
  public function factsQuery($node, $type) {
    $facts_query = $this->nodeQuery($type);
    $reference_condition = $facts_query->orConditionGroup()
      ->condition('field_ml_state', $node->id())
      ->condition('field_ml_component', $node->id());
    $facts_query->condition($reference_condition)
      ->condition('status', NodeInterface::PUBLISHED);
    $facts = Node::loadMultiple($facts_query->execute());
    return $facts;
  }

  /**
   * Return the score node for a state-component pair.
   */
  public function getScoreNode($state, $component) {
    $component_score_query = $this->nodeQuery('ml_state_component_score')
      ->condition('field_ml_state', $state->id())
      ->condition('field_ml_component', $component->id())
      ->condition('status', NodeInterface::PUBLISHED);
    $component_score = $this->getFirstNode($component_score_query);
    return $component_score;
  }

  /**
   * Return an array of state points data.
   */
  public function getRating($state, $component) {
    if ($score_node = $this->getScoreNode($state, $component)) {
      $rating = [
        'rating' => $score_node->field_ml_state_component_score->value,
        'weight' => $component->field_ml_weight->value,
      ];
      return $rating;
    }
  }

  /**
   * Return the ratings for the components for the given state.
   */
  public function getRatings($state) {
    // After weighting each of the 21 components, we rated every state on the
    // components on a scale of 0 to 4.
    $components = $this->getComponents();
    $ratings = array_map(function ($component) use ($state) {
      return $this->getRating($state, $component);
    }, $components);
    return $ratings;
  }

  /**
   * Return the sum of the weighted ratings.
   */
  public function getSubScore($ratings) {
    // We multiplied the rating and the weight to get a score for each component
    // in each state.
    $component_scores = array_map(function ($rating) {
      return $rating['rating'] * $rating['weight'];
    }, $ratings);
    // We then added up the scores for each of the components and came up with
    // an overall score for each state.
    $subscore = array_sum($component_scores);
    return $subscore;
  }

  /**
   * Return the maximum possible score based on which ratings are NULL.
   */
  public function getStateMaxPossibleScore($ratings) {
    // For those states that allow full-time virtual charter schools, the
    // highest score possible is 240 for all 21 components. For those states
    // that don’t allow full-time virtual charter schools, the highest score
    // possible is 228 for the remaining 20 components.
    $max_possible_component_scores = array_map(function ($rating) {
      $max_possible_rating = $rating['rating'] == NULL ? 0 : self::MAX_POINTS;
      return $max_possible_rating * $rating['weight'];
    }, $ratings);
    $max_possible_score = array_sum($max_possible_component_scores);
    return $max_possible_score;
  }

  /**
   * Calculate the score for a state.
   */
  public function getStateScore($state) {
    $ratings = $this->getRatings($state);
    $subscore = $this->getSubScore($ratings);
    $max_possible_score = $this->getStateMaxPossibleScore($ratings);
    // We converted these scores to ones that are comparable to the states that
    // allow full-time virtual charter schools. For example, Maryland received
    // 48 out of the 228 points available for the remaining 20 components, or 21
    // percent.
    $percentage = $subscore / $max_possible_score;
    // We then multiplied the total points possible for all 21
    // components (240) by 21 percent to get a score comparable to the other
    // states (51).
    $score = round($this->getMaxScore() * $percentage);
    return $score;
  }

  /**
   * Update the score for a state.
   */
  public function updateStateScore($state) {
    $score = $this->getStateScore($state);
    if ($score != NULL) {
      $state->set('field_ml_state_score', [$score]);
      $state->save();
      \Drupal::messenger()->addStatus(t('The overall score for %state has been recalculated.', ['%state' => $state->toLink($state->getTitle())->toString()]), FALSE);
    }
  }

  /**
   * Return (and possibly calculate) the maximum score.
   */
  public function getMaxScore($reset = FALSE) {
    $max = $this->state->get('napcs_model_law.max_score', FALSE);
    if (!$max || $reset) {
      $components = $this->getComponents();
      $max = array_reduce($components, function ($total, $component) {
        $total += $component->field_ml_weight->value * self::MAX_POINTS;
        return $total;
      }, 0);
      $this->state->set('napcs_model_law.max_score', $max);
      \Drupal::messenger()->addStatus(t('The maximum score has been updated.'));
    }
    return $max;
  }

  /**
   * Return a state subcomponent status node.
   */
  public function getStatus($state, $component, $group_delta, $subcomponent_delta) {
    $subcomponent_status_query = $this->nodeQuery('ml_state_subcomponent_status')
      ->condition('field_ml_state', $state->id())
      ->condition('field_ml_component', $component->id())
      ->condition('field_ml_subcomponent_group_num', $group_delta)
      ->condition('field_ml_subcomponent_num', $subcomponent_delta)
      ->condition('status', NodeInterface::PUBLISHED);
    $subcomponent_status = $this->getFirstNode($subcomponent_status_query);
    return $subcomponent_status;
  }

  /**
   * Return the title of a group item.
   */
  public function getGroupTitle($group) {
    $title_items = $group->get('field_ml_group_title');
    if ($title_items->count()) {
      $title = $title_items->first()->view();
    }
    else {
      $title = '';
    }
    return $title;
  }

  /**
   * Get the first node resulting from an entity query.
   */
  protected function getFirstNode($query) {
    $results = $query->execute();
    if ($results) {
      return Node::load(array_shift($results));
    }
    return NULL;
  }

  /**
   * Return an entity query for the given node type.
   */
  protected function nodeQuery($type) {
    return $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', $type);
  }

}

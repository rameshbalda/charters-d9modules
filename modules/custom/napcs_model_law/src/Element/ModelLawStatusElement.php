<?php

namespace Drupal\napcs_model_law\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\Component\Utility\Html;

/**
 * Provides a Model Law Status render element.
 *
 * @RenderElement("model_law_status")
 */
class ModelLawStatusElement extends RenderElement {

  const STATUSES = ['No', 'Some', 'Yes'];

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#theme' => 'model_law_status',
      '#pre_render' => [
        [self::class, 'preRender'],
      ],
      '#status' => 'N/A',
      '#attributes' => [
        'class' => [],
      ],
    ];
  }

  /**
   * Pre-render callback.
   */
  public static function preRender($element) {
    $statuses = self::STATUSES;
    if (isset($statuses[$element['#status']])) {
      $element['#status'] = self::STATUSES[$element['#status']];
    }
    else {
      $element['#status'] = 'N/A';
    }
    $element['#attributes']['class'][] = 'model-law-status';
    $element['#attributes']['class'][] = 'model-law-status--' . Html::cleanCssIdentifier(strtolower($element['#status']));
    return $element;
  }

}

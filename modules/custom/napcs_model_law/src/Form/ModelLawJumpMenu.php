<?php

namespace Drupal\napcs_model_law\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\napcs_model_law\ModelLawData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a jump menu to model law entities.
 */
class ModelLawJumpMenu extends FormBase {

  /**
   * The Model Law data service.
   *
   * @var \Drupal\napcs_model_law\ModelLawData
   */
  protected $modelLawData;

  /**
   * Class constructor.
   */
  public function __construct(ModelLawData $model_law_data) {
    $this->modelLawData = $model_law_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('napcs_model_law.data')
    );
  }

  /**
   * Counter to increment for each new jump menu.
   *
   * @var int
   */
  private static $uid = 0;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    $id = 'jump_menu_' . self::$uid;
    self::$uid++;
    return $id;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    [$type, $title] = $form_state->getBuildInfo()['args'];
    $nodes = $this->modelLawData->$type();
    $form['jump'] = [
      '#type' => 'select',
      '#options' => $this->getJumpOptions($nodes),
      '#empty_option' => $title,
      '#attributes' => [
        'onchange' => 'window.location.href = this.value',
      ],
    ];
    return $form;
  }

  /**
   * Return options for the jump menu.
   *
   * @param \Drupal\node\NodeInterface[] $nodes
   *   An array of nodes to become jump options.
   *
   * @return string[]
   *   An array of node titles, keyed by their urls.
   */
  protected function getJumpOptions($nodes) {
    return array_reduce($nodes, function ($options, $node) {
      $options[$node->toUrl()->toString()] = $node->getTitle();
      return $options;
    }, []);
  }

  /**
   * The jump element has its own submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}

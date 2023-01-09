<?php

namespace Drupal\napcs_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a two-column content block for the NAPCS homepage.
 *
 * @Block(
 *   id = "napcs_block_homepage_2col",
 *   admin_label = @Translation("Homepage 2 Columns"),
 *   category = @Translation("NAPCS Block"),
 * )
 */
class Homepage2Col extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Construct the block plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $columns = [
      [
        'icon' => 'chart-arrow-up',
        'title' => t('Explore the Charter School Data Dashboard'),
        'content' => 'Our Charter School Data Dashboard provides statistics on enrollment, numbers of schools, breakdown of type of charter school, and management structure. View the data nationally, by state, by management organization, by authorizer, or by school.',
        'url' => Url::fromUri('http://data.publiccharters.org/'),
        'link_text' => t('VIEW THE DATA DASHBOARD&nbsp;<img alt="" src="/themes/custom/napcs2017/assets/img/right-red-arrow.png" />'),
        'link_attributes' => [
          'target' => '_blank',
        ],
      ],
      [
        'icon' => 'lines-check',
        'title' => t('Learn How Your State Ranks the Charter School Model Law'),
        'content' => 'To help states create laws that support high-quality public charter schools, we developed a model state law. Each year, we rank states based on how well their laws align to this model.',
        'url' => Url::fromUri('internal:/our-work/charter-law-database'),
        'link_text' => t('VIEW MODEL LAW <img alt="" src="/themes/custom/napcs2017/assets/img/right-red-arrow.png" />'),
        'footer_right' => $this->formBuilder->getForm('\Drupal\napcs_model_law\Form\ModelLawJumpMenu', 'states', 'Choose a state'),
      ],
    ];

    return [
      '#theme' => 'napcs_block_homepage_2col',
      '#columns' => $columns,
    ];
  }

}

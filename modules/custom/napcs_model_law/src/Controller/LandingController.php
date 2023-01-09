<?php

namespace Drupal\napcs_model_law\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\napcs_model_law\ModelLawData;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for Model Law landing page.
 */
class LandingController implements ContainerInjectionInterface {

  /**
   * The Model Law data service.
   *
   * @var Drupal\napcs_model_law\ModelLawData
   */
  protected $dataService;

  /**
   * The form builder service.
   *
   * @var Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The view builder for nodes.
   *
   * @var Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewBuilder;

  /**
   * The URI of the model law section.
   *
   * @var string
   */
  protected $baseUri = 'internal:/our-work/charter-law-database';

  /**
   * The node id of the current model law report publication.
   *
   * @var int
   */
  protected $report_nid = 29220;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $form_builder, ModelLawData $data_service) {
    $this->formBuilder = $form_builder;
    $this->dataService = $data_service;
    $this->nodeViewBuilder = $entity_type_manager->getViewBuilder('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Inject entity type manager, form builder, and model law data service.
    return new static(
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('napcs_model_law.data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content() {
    // Create states block.
    $blocks['states'] = [
      'header' => $this->tag(['#markup' => 'How does my state measure up?'], 'h2'),
      'blurb' => $this->tag(
        ['#markup' => 'Get the score for each state, including detailed analyses for each of the model law components.'],
        'p'
      ),
      'button' => $this->button('View state rankings', $this->baseUri . '/states'),
      'jump_menu' => $this->jumpMenu('states', 'Or, choose a state:'),
    ];

    // Create components block.
    $blocks['components'] = [
      'header' => $this->tag(['#markup' => 'What makes up the model law?'], 'h2'),
      'blurb' => $this->tag(
        ['#markup' => 'See the full list of components, then drill down to learn how we evaluate them and and learn how the states stack up for each one.'],
        'p'
      ),
      'button' => $this->button('View model law components', $this->baseUri . '/components'),
      'jump_menu' => $this->jumpMenu('components', 'Or, choose a component:'),
    ];

    // Create FAQ Block.
    $blocks['faq'] = [
      'header' => $this->tag(['#markup' => 'FAQ'], 'h2'),
      'list' => [
        '#theme' => 'item_list',
        '#type' => 'ul',
        '#items' => [
          $this->faqLink('What is the model law?', 'modellaw'),
          $this->faqLink('How are scores calculated?', 'scores'),
          $this->faqLink('Why doesn\'t my state have a score?', 'statescore'),
        ],
      ],
      'button' => $this->button('View all FAQ', $this->faqUri()),
    ];

    // Wrap each block with grid and identifying classes.
    foreach ($blocks as $name => $element) {
      $blocks[$name] = $this->tag($element, 'div', ['col-md-4', "model-law-landing-$name-block ml-blk"]);
    }

    // Wrap all blocks in grid row.
    $blocks = $this->tag($blocks, 'div', ['row']);

    // Load publication node.
    $pub_node = Node::load($this->report_nid);
    // Get publication node render array using publication_featured view mode.
    $pub_view = $this->nodeViewBuilder->view($pub_node, 'publication_featured');
    $pub_col = [
      'header' => $this->tag($pub_node->get('title')->view(), 'h2'),
      'pub' => $pub_view,
    ];
    // Wrap title and node in column and row classes.
    $pub_col = $this->tag($pub_col, 'div', ['col-md-12']);
    $pub_row = $this->tag([$pub_col], 'div', ['row']);

    $build = [$blocks, $pub_row];

    return $build;
  }

  /**
   * Wrap an element with an HTML tag.
   *
   * @param array|\Drupal\Core\Render\RenderElement $element
   *   A render array or render element.
   * @param string $tag
   *   The name of an HTML tag.
   * @param string[] $classes
   *   (optional) An array of classes.
   *
   * @return array|\Drupal\Core\Render\RenderElement
   *   The render element with #prefix and #suffix added.
   */
  protected function tag($element, $tag, array $classes = []) {
    $element['#prefix'] = "<$tag";
    if ($classes) {
      $classes = implode(' ', $classes);
      $element['#prefix'] .= " class=\"$classes\"";
    }
    $element['#prefix'] .= '>';
    $element['#suffix'] = "</$tag>";
    return $element;
  }

  /**
   * Return button render array.
   *
   * @param string $text
   *   The text of the button.
   * @param string $uri
   *   The destination of the button.
   * @param string[] $classes
   *   (optional) An array of classes.
   *
   * @return array
   *   A render array for a link-styled button.
   */
  protected function button($text, $uri, array $classes = []) {
    $default_classes = ['btn', 'btn-default'];
    $classes = array_merge($classes, $default_classes);
    return [
      '#type' => 'link',
      '#title' => $this->buttonText($text),
      '#url' => Url::fromUri($uri),
      '#attributes' => [
        'class' => $classes,
      ],
    ];
  }

  /**
   * Return button text (add a right-arrow icon and sanitize).
   */
  protected function buttonText($text) {
    return t('@text&nbsp;<i class="fa fa-arrow-right"></i>', ['@text' => $text]);
  }

  /**
   * Return the URI to the FAQ page.
   */
  protected function faqUri() {
    return $this->baseUri . '/charter-law-rankings-faq';
  }

  /**
   * Return a link to an anchor on the FAQ page.
   */
  protected function faqLink($text, $anchor) {
    $url = Url::fromUri($this->faqUri(), ['fragment' => $anchor]);
    $link = Link::fromTextAndUrl($text, $url);
    return $link;
  }

  /**
   * Return a jump menu form.
   */
  protected function jumpMenu($type, $title) {
    return $this->formBuilder->getForm('Drupal\napcs_model_law\Form\ModelLawJumpMenu', $type, $title);
  }

}

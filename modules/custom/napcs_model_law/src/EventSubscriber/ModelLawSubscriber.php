<?php

namespace Drupal\napcs_model_law\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\node\Entity\Node;

/**
 * Event subscriber for model law functionality.
 */
class ModelLawSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::PRE_ROW_SAVE][] = ['setStateComponentTitle'];
    return $events;
  }

  /**
   * Set state component score titles.
   */
  public function setStateComponentTitle(MigratePreRowSaveEvent $event) {
    if ($event->getMigration()->id() == 'napcs_ml_state_component') {
      $row = $event->getRow();
      $title_properties = ['state', 'component'];
      $title_parts = array_map(function ($property) use ($row) {
        $nid = $row->getSourceProperty($property);
        $title = Node::load($nid)->get('title')->first()->getValue()['value'];
        return $title;
      }, $title_properties);
      $title = implode(': ', $title_parts);
      $row->setSourceProperty('title', $title);
    }
  }

}

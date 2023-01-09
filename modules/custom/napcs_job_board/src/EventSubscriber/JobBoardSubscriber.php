<?php

namespace Drupal\napcs_job_board\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\node\Entity\Node;

/**
 * Subscribe to MigratePostRowSaveEvent to add profile nodes to user accounts.
 */
class JobBoardSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE][] = ['userProfileUpdate'];
    return $events;
  }

  /**
   * Add profiles to their users.
   */
  public function userProfileUpdate(MigratePostRowSaveEvent $event) {
    if ($event->getMigration()->id() == 'napcs_job_board_profile') {
      $profile_nid = array_shift($event->getDestinationIdValues());
      // Get the author of the node, and set that user's profile field to
      // reference the node.
      Node::load($profile_nid)->getOwner()->set('field_profile', $profile_nid)->save();
    }
  }

}

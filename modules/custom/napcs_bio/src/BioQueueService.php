<?php

namespace Drupal\napcs_bio;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\NodeInterface;

/**
 * Provide easy access to queues.
 */
class BioQueueService implements BioQueueServiceInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * An array of queue ids to check.
   *
   * @var string[]
   */
  protected $queueIds = [
    'board_bios',
    'staff_bios',
  ];

  /**
   * Constructs a new BioQueueService object.
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueue(NodeInterface $node) {
    foreach ($this->queueIds as $queue_id) {
      if ($this->inQueue($queue_id, $node)) {
        return str_replace('_bios', '', $queue_id);
      }
    }
    return '';
  }

  /**
   * Return a boolean indicating if a queue contains a node.
   *
   * @param string $queue_id
   *   A queue machine name.
   * @param \Drupal\node\NodeInterface $node
   *   A node entity object.
   *
   * @return bool
   *   TRUE if the queue contains the node.
   */
  protected function inQueue($queue_id, NodeInterface $node) {
    if ($queue = $this->loadQueue($queue_id)) {
      $items = array_map(function ($item) {
        return $item['target_id'];
      }, $queue->get('items')->getValue());
      return in_array($node->id(), $items);
    }
    return FALSE;
  }

  /**
   * Return an subqueue.
   *
   * @param string $queue_id
   *   A queue machine name.
   *
   * @return \Drupal\entityqueue\EntitySubqueueInterface
   *   An entity subqueue.
   */
  protected function loadQueue($queue_id) {
    // https://drupal.stackexchange.com/questions/201937/what-is-the-appropriate-way-to-get-items-from-an-entityqueue
    return $this->entityTypeManager->getStorage('entity_subqueue')->load($queue_id);
  }

}

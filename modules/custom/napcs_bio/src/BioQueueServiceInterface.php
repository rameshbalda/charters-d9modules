<?php

namespace Drupal\napcs_bio;

use Drupal\node\NodeInterface;

/**
 * Interface for the bio queue service.
 */
interface BioQueueServiceInterface {

  /**
   * Return a queue id of a queue that the node is in.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node entity.
   *
   * @return string
   *   A queue id if available, or empty string.
   */
  public function getQueue(NodeInterface $node);

}

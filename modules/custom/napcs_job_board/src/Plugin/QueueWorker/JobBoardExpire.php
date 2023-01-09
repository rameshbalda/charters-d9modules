<?php

namespace Drupal\napcs_job_board\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Expires job board listings in the queue.
 *
 * @QueueWorker(
 *   id="job_board_expire",
 *   title=@Translation("Job board expire"),
 *   cron={"time"=15}
 * )
 */
class JobBoardExpire extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $node = \Drupal::entityTypeManager()->getStorage('node')
      ->load($data);
    if (!$node->isPublished()) {
      return;
    }
    $node->setUnpublished()->save();
    \Drupal::logger('napcs_job_board')->info('Job listing "{title}" expired.', ['title' => $node->getTitle()]);
  }

}

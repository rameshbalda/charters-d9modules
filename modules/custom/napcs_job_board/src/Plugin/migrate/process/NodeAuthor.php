<?php

namespace Drupal\napcs_job_board\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;

/**
 * Return the user id of a node's author.
 *
 * @MigrateProcessPlugin(
 *   id = "node_author"
 * )
 */
class NodeAuthor extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $author = Node::load($value)->get('uid')->referencedEntities();
    return $author;
  }

}

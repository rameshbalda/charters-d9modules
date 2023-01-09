<?php

/**
 * @file
 * napcs_migrate.deploy.php
 */

use Drupal\Component\Utility\Html;
use Drupal\node\Entity\Node;

/**
 * Attempt to format a date.
 */
function _napcs_migrate_webinar_date_format($date_content) {
  try {
    $date_string = str_replace(['ET', ' AM', ' PM'], ['America/New_York', 'AM', 'PM'], preg_replace('/[^\w:]+/', ' ', $date_content));
    $date_obj = new DateTime($date_string, new DateTimeZone('America/New_York'));
    $date_obj->setTimezone(new DateTimeZone('UTC'));
    return $date_obj->format('Y-m-d\TH:i:s');
  }
  catch (Exception $e) {
    throw new Exception(print_r([
      $e->getMessage(),
      $date_content,
      $date_string,
    ], TRUE));
  }
}

/**
 * Migrate webinar page to new webinar content architecture.
 */
function napcs_migrate_deploy_webinar_content(&$sandbox) {
  $direct_fields = [
    'meet_a_charter_student' => [
      'field_student_image' => 'field_webinar_image',
      'field_meet_a_student_link' => 'field_webinar_register',
    ],
    'video_full_width' => [
      'field_video_link' => 'field_webinar_video',
    ],
  ];
  $parent_node = Node::load(17344);
  $paragraphs = $parent_node->field_paragraphs->referencedEntities();
  foreach ($paragraphs as $paragraph) {
    $bundle = $paragraph->bundle();
    $webinar_node = Node::create(['type' => 'webinar']);
    foreach ($direct_fields[$bundle] as $src => $dst) {
      $webinar_node->$dst->setValue($paragraph->$src->getValue());
    }
    switch ($bundle) {
      case 'meet_a_charter_student':
        $content = $paragraph->field_student_bio->value;
        $dom = Html::load($content);
        $webinar_node->title->value = $dom->getElementsByTagName('h2')->item(0)->textContent;
        $grafs = $dom->getElementsByTagName('p');
        $webinar_node->field_webinar_date->value = _napcs_migrate_webinar_date_format($grafs->item(0)->textContent);
        $webinar_node->body->value = $grafs->item(1)->textContent;
        break;

      case 'video_full_width':
        $title_content = $paragraph->field_section_title->value;
        list($title, $date_content) = explode('—', $title_content);
        $webinar_node->title->value = $title;
        $webinar_node->field_webinar_date->value = _napcs_migrate_webinar_date_format($date_content);
        break;

    }
    $webinar_node->save();
  }
  $parent_node->field_paragraphs->setValue(NULL);
  $parent_node->save();
}

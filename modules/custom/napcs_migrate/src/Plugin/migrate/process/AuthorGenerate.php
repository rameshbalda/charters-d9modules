<?php

namespace Drupal\napcs_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityGenerate;
use Drupal\migrate_plus\Plugin\migrate\process\EntityLookup;
use TheIconic\NameParser\Parser as NameParser;

/**
 * Plugin to generate adequate defaults for blog authors.
 *
 * @MigrateProcessPlugin(
 *   id = "author_generate"
 * )
 */
class AuthorGenerate extends EntityGenerate {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrateExecutable, Row $row, $destinationProperty) {
    $results = [];
    $data = $this->getValues($value);
    foreach ($data as $datum) {
      $title = trim(implode(' ', [$datum['field_first_name'], $datum['field_last_name']]));
      if (!($result = EntityLookup::transform($title, $migrateExecutable, $row, $destinationProperty))) {
        $result = $this->generateEntity($datum);
      }
      $results[] = $result;
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  protected function entity($value) {
    $entity_values = parent::entity($value['name']);
    $entity_values += $value;
    return $entity_values;
  }

  /**
   * Parse an author value from the source into an array of data.
   *
   * @param string $value
   *   The source value.
   *
   * @return array[]
   *   A nested array with name and job data.
   */
  protected function getValues($value) {
    // Everything after the comma is job info.
    $parts = explode(', ', $value);
    // Not calling this "name" cuz it might be two names.
    $first = array_shift($parts);
    // These are expected to be names, but these items are gonna be mapped to
    // arrays of field data.
    $data = explode(' and ', $first);
    // If there are not multiple names, just use the original string.
    if (count($data) < 2) {
      $data = [$first];
    }
    // Map to array.
    foreach ($data as $i => $name) {
      $data[$i] = ['name' => $name];
    }
    if ($job = array_shift($parts)) {
      $data[0]['job'] = $job;
    }
    foreach ($data as $i => $datum) {
      // Anything mentioning NAPCS converted to "National Alliance".
      if (strpos($datum['name'], 'NAPCS') !== FALSE) {
        $data[$i]['name'] = 'National Alliance';
        $data[$i]['field_first_name'] = 'National';
        $data[$i]['field_last_name'] = 'Alliance';
      }
      else {
        // Get name fields.
        $parsed = $this->parseName($datum['name']);
        $data[$i]['field_first_name'] = $this->buildFirstName($parsed);
        $data[$i]['field_last_name'] = $this->buildLastName($parsed);
        if (isset($value['job'])) {
          // Get job/org fields.
          $job_info = $this->getJobInfo($value['job']);
          $data[$i]['field_job_title_position'] = $job_info['title'];
          $data[$i]['field_bio_organization'] = $job_info['org'];
        }
      }
    }
    return $data;
  }

  /**
   * Parse a name and return the parsed object.
   */
  protected function parseName($name) {
    static $parser;
    if (!isset($parser)) {
      $parser = new NameParser();
    }
    return $parser->parse($name);
  }

  /**
   * Build a first name from a parsed object.
   */
  protected function buildFirstName($parsed) {
    $parts = [
      'salutation',
      'firstname',
      'middlename',
      'initials',
    ];
    $first_name = $this->buildName($parsed, $parts);
    return $first_name;
  }

  /**
   * Build a last name from a parsed name object.
   */
  protected function buildLastName($parsed) {
    $parts = [
      'lastname',
      'suffix',
    ];
    $last_name = $this->buildName($parsed, $parts);
    return $last_name;
  }

  /**
   * Build a name from a parsed name object and a list of parts to concatenate.
   */
  protected function buildName($parsed, $parts) {
    $names = [];
    foreach ($parts as $part) {
      $method = 'get' . ucfirst($part);
      if ($name = $parsed->$method()) {
        $names[] = trim($name);
      }
    }
    $name = implode(' ', $names);
    return $name;
  }

  /**
   * Extract job information from a fragment of source data.
   */
  protected function getJobInfo($job_data) {
    if (strpos(' of ', $job_data)) {
      list($title, $org) = explode(' of ', $job_data);
    }
    elseif (strpos(' from ', $job_data)) {
      list($title, $org) = explode(' from ', $job_data);
    }
    else {
      list($org, $title) = explode(' ', $job_data, 2);
    }
    return compact($title, $org);
  }

}

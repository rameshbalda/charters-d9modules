<?php

namespace Drupal\napcs_job_board\Plugin\migrate\source;

use Drupal\Component\Utility\UrlHelper;
use Drupal\migrate\Row;

/**
 * Source plugin for Organizations.
 *
 * @MigrateSource(
 *   id = "job_board_organization"
 * )
 */
class Organization extends JobBoardSourceBase {

  /**
   * List of states from address module.
   *
   * @var array
   */
  protected $states;

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->organizationQuery([
      'id',
      'user_id',
      'company_name',
      'company_website',
      'company_info',
      'company_logo_ext',
      'company_country',
      'company_state',
      'company_zip_code',
      'company_location',
      'is_public',
    ]);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => t('ID'),
      'user_id' => t('Author ID'),
      'company_name' => t('Title'),
      'company_website' => t('Website'),
      'company_info' => t('Description'),
      'company_logo_ext' => t('Logo extension'),
      'company_country' => t('Country'),
      'company_state' => t('State'),
      'company_zip_code' => t('Zip code'),
      'company_location' => t('Location'),
      'is_public' => t('Status'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'integer',
        'alias' => 'e',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if ($state_data = $row->getSourceProperty('company_state')) {
      if ($state = $this->getStateCode($state_data)) {
        $row->setSourceProperty('state', $state);
      }
    }
    if ($website = $row->getSourceProperty('company_website')) {
      $website = $this->checkWebsiteUrl($website);
      $row->setSourceProperty('company_website', $website);
    }
  }

  /**
   * Ensure given url is valid, return NULL if not.
   */
  protected function checkWebsiteUrl($url) {
    $url = preg_replace('#:///*#', '://', $url);
    if (UrlHelper::isValid($url)) {
      return $url;
    }
    return NULL;
  }

  /**
   * Return a state code for a given row.
   */
  protected function getStateCode($state_data) {
    $state = '';
    $states = $this->getStates();
    // Data is already a two-letter state code.
    if (in_array($state_data, array_keys($states))) {
      $state = $state_data;
    }
    // Code or state name is substring of data. Includes the case when the state
    // data is a valid state name.
    elseif ($code = $this->checkStateSubstr($state_data)) {
      $state = $code;
    }
    return $state;
  }

  /**
   * Return two-letter state code if state data contains state code or name.
   */
  protected function checkStateSubstr($state_data) {
    foreach ($this->getStates() as $code => $state) {
      $code_in_data = strpos($state_data, $code) !== FALSE;
      $state_name_in_data = strpos($state_data, $state) !== FALSE;
      if ($code_in_data || $state_name_in_data) {
        return $code;
      }
    }
  }

  /**
   * Return a list of states from the address module.
   */
  protected function getStates() {
    if (!isset($this->states)) {
      $this->states = \Drupal::service('address.subdivision_repository')->getList(['US']);
    }
    return $this->states;
  }

}

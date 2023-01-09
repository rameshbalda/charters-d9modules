<?php

namespace Drupal\napcs_job_board;

use Drupal\node\Entity\Node;
use Drupal\views\ViewExecutable;

/**
 * Job board hook helpers class.
 */
class NJBHookHelpers {

  /**
   * The id of our main jobs listing view.
   *
   * @var string
   */
  public static $viewID = 'napcs_job_listings';

  /**
   * The text that we want to appear when the job is flexible.
   *
   * @var string
   */
  public static $flexText = 'Flexible Location';

  /**
   * Log a message to watchdog.
   *
   * @param mixed $message
   *   Anything that can be logged.
   * @param string $custom_type
   *   A string representing a custom log message type, defaults to "njb".
   */
  public static function log($message = 'NULL', $custom_type = 'njb') {
    \Drupal::logger($custom_type)->notice(json_encode($message, JSON_PRETTY_PRINT));
  }

  /**
   * Checks a view executable to see if the id is the 'napcs_job_listings' id.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   An instance of the ViewExecutable class.
   *
   * @return bool
   *   True if the view's id is napcs_job_listings. false otherwise.
   */
  public static function isView(ViewExecutable $view) {
    return $view->id() == self::$viewID;
  }

  /**
   * Gets from the current url a query paramter.
   *
   * @param string $name
   *   A key to get.
   *
   * @return object
   *   Returns a Drupal value format.
   */
  public static function getRequestValue(string $name) {
    return self::getRequest()->get($name);
  }

  /**
   * Return an array of request parameters.
   */
  public static function getAllRequestParameters() {
    return self::getRequest()->query->all();
  }

  /**
   * Gets the current request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The Symfony request object.
   */
  public static function getRequest() {
    return \Drupal::request();
  }

  /**
   * Load a node.
   */
  public static function nload(int $id) {
    return Node::load($id);
  }

  /**
   * Check if node has legacy location.
   */
  public static function hasLegacyLocation(int $id) {
    $node = self::nLoad($id);
    $field = 'field_legacy_location_value';
    return $node->hasField($field) && !empty($node->get($field));
  }

  /**
   * Checks from the address field if the field has the key we need.
   *
   * @param int $id
   *   The node id.
   * @param string $field
   *   The key to check.
   *
   * @return bool
   *   True if the node has the give address field key.
   */
  public static function addressHas(int $id, $field = '') {
    $field = 'administrative_area';
    if (!($address = self::loadAddressValues($id)) || empty($field)) {
      return FALSE;
    }
    return self::arrayHas($address, $field);
  }

  /**
   * Retrieves the node object and from that gets the address values.
   *
   * @param int $id
   *   The node id.
   *
   * @return bool|array
   *   Array of values or false.
   */
  private static function loadAddressValues(int $id) {
    $node = self::nLoad($id);
    return self::getAddressValues($node);
  }

  /**
   * Gets the array of address values from the FieldsItemList interface.
   *
   * @param object $node
   *   The ContentTypeInterface for the node object.
   *
   * @return bool|array
   *   Array of values or false.
   */
  public static function getAddressValues($node) {
    if (empty($node)) {
      return FALSE;
    }

    if ($node->hasField('field_address')) {
      $field_address = $node->get('field_address')->getValue();
      if (!empty($field_address)) {
        return $field_address[0];
      }
    }

    return FALSE;
  }

  /**
   * Retrieves from the field_address a specific value. Or false on failure.
   *
   * @param int $id
   *   The node id.
   * @param string $field
   *   The key.
   *
   * @return string|bool
   *   The value of the field.
   */
  public static function getAddressValue(int $id, $field = '') {
    $address = self::loadAddressValues($id);
    if ($address === FALSE || empty($field)) {
      return FALSE;
    }
    return $address[$field];
  }

  /**
   * Gets the value of flexible location.
   *
   * @param int $nid
   *   The node id of the row.
   *
   * @return string|bool
   *   Value of field flexible location or false.
   */
  public static function isFlexible(int $nid) {
    $node = self::nLoad($nid);

    if ($node->hasField('field_flexible_location')) {
      $value = $node->get('field_flexible_location')->getValue();
      if (!empty($value) && isset($value[0]['value'])) {
        return $value[0]['value'];
      }
    }

    return FALSE;
  }

  /**
   * Appends text that indicates that the field is flexible to the output.
   *
   * @param string &$output
   *   Text that is being displayed in the view.
   * @param string $sep
   *   The separator to prepend before out flex text.
   */
  public static function appendFlexible(&$output, $sep = ', ') {
    $output .= $sep . self::$flexText;
  }

  /**
   * Check if the nid has the required data to display the address field.
   *
   * @param int $nid
   *   The id of the row's node.
   *
   * @return bool
   *   True if the node has the required address field data.
   */
  public static function hasAddressField(int $nid) {
    $hasState = self::addressHas($nid, 'administrative_area');
    $hasLocality = self::addressHas($nid, 'locality');

    return $hasState || $hasLocality;
  }

  /**
   * A simple utility function that checks if an assoc array has a field.
   *
   * @param array $array
   *   Array to check.
   * @param string $field
   *   Key to check.
   *
   * @return bool
   *   True if the array has a non-empty value for the given key.
   */
  private static function arrayHas(array $array, $field) {
    return isset($array[$field]) && !empty($array[$field]);
  }

}

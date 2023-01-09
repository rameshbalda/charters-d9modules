<?php

namespace Drupal\napcs_twig\Twig;

use Drupal\napcs_job_board\NJBAccessHelpers as Access;
use Drupal\napcs_job_board\NJBHookHelpers as Helper;
use Drupal\Core\Render\Element;

/**
 * Provide NAPCS twig extension functionality.
 */
class NAPCSTwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'napcs_twig';
  }

  /**
   * We declare the extension functions.
   */
  public function getFunctions() {
    return [
      $this->makeSimpleFunc('getProfileName'),
      $this->makeSimpleFunc('userHasProfilePermission'),
      $this->makeSimpleFunc('getJobTitle'),
      $this->makeSimpleFunc('getListingBackParams'),
      $this->makeSimpleFunc('getListingAddress'),
    ];
  }

  /**
   * Return a Twig_SimpleFunction object for the given function name.
   */
  private function makeSimpleFunc($funcName, $safe = TRUE) {
    $args = [];
    if ($safe) {
      $args['is_safe'] = ['html'];
    }
    return new \Twig_SimpleFunction($funcName,
        [$this, $funcName],
        $args
      );
  }

  /**
   * Return url parameters for a back button or link.
   */
  public function getListingBackParams() {
    $req = \Drupal::request()->query->all();
    if (!empty($req['back'])) {
      $values = $req['back'];
      $params = [];
      foreach ($values as $key => $value) {
        $tmp = '';
        if (is_array($value)) {
          foreach ($value as $index => $v) {
            $tmp .= urlencode($key) . '[' . $index . ']=' . urlencode($v);
          }
        }
        else {
          $tmp .= urlencode($key) . '=' . urlencode($value);
        }
        array_push($params, $tmp);
      }
      return '?' . implode('&', $params);
    }
    return '';
  }

  /**
   * Take a node id - presumably for the profile.
   *
   * @deprecated
   *   Use getTitle().
   */
  public function getProfileName(int $nid) {
    return $this->getTitle($nid);
  }

  /**
   * Get the title of a job post.
   *
   * @deprecated
   *   Use getTitle().
   */
  public function getJobTitle(int $nid) {
    return $this->getTitle($nid);
  }

  /**
   * Return the node title of the given nid.
   */
  public function getTitle($nid) {
    $node = Helper::nload($nid);
    return $node ? $node->getTitle() : FALSE;
  }

  /**
   * Gets the current user's.
   */
  public function userHasProfilePermission(int $uid, $nid, $permission = '') {
    if (Access::getFirstTarget($uid, 'field_profile') == $nid) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Return address display output.
   *
   * @param object $content
   *   The content info with fields.
   *
   * @return string         output to display in twig.
   */
  public function getListingAddress($content) {
    $address = $this->getFieldValue($content, 'field_address');
    $legacyLocation = $this->getFieldValue($content, 'field_legacy_location');
    $flexible = $this->getFieldValue($content, 'field_flexible_location');

    $output = [];
    if ($address) {
      // Lets purge our values and only include what we want.
      foreach (Element::children($address[0]) as $element) {
        $show_field = in_array($element, ['administrative_area', 'locality']);
        if ($show_field) {
          continue;
        }
        $address[0][$element]['#value'] = NULL;
      }
      $output[0] = $address;
    }
    elseif ($legacyLocation) {
      $output[0] = $legacyLocation;
    }

    $output[0]['#title'] = '';

    if ($flexible) {
      $output[1] = $flexible;
    }

    return $output;
  }

  /**
   * Makes sure a field exists before returning. Evaluates base on
   * the convention of a field output retrieved from twig.
   *
   * @param array $array
   *   an associative array.
   * @param string $value
   *   any key that we want to try and retrieve.
   *
   * @return mixed  False if empty, assoc array if not.
   */
  private function getFieldValue($array, $value) {
    return isset($array[$value]) &&
      isset($array[$value][0]) &&
      $array[$value][0] !== NULL ? $array[$value] : FALSE;
  }

}

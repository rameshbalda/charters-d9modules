<?php

namespace Drupal\napcs_migrate;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;

/**
 * Class FileMigrateHelperService.
 */
class FileMigrateHelperService implements FileMigrateHelperInterface {

  /**
   * The file storage service.
   *
   * @var Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Drupal\Core\File\FileSystem definition.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * An array of already-downloaded urls mapped to thier new destinations.
   *
   * @var string[]
   */
  protected $files;

  /**
   * Constructs a new FileMigrateHelperService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FileSystem $file_system) {
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public function getFile($src) {
    if ($cached = $this->getCachedFile($src)) {
      return $cached;
    }
    $dest = $this->getFileDestination($src);
    $uri = $dest . '/' . $this->fileSystem->basename(parse_url($src, PHP_URL_PATH));
    if ($local = $this->loadFile($uri)) {
      $this->cacheFile($src, $local);
      return $local;
    }
    return $this->downloadFile($src, $dest);
  }

  /**
   * Return a URL corresponding to the file downloaded from $src.
   *
   * @param string $src
   *   The file to download.
   *
   * @return string
   *   The URL of the file from the cache variable, or empty string if it
   *   doesn't exist.
   */
  protected function getCachedFile($src) {
    return $this->files[$src] ?? '';
  }

  /**
   * Return a stream URI destination for a migrated file.
   *
   * @param string $src
   *   The URL of the file to be migrated.
   *
   * @return string
   *   A stream URI representing the destination.
   */
  public function getFileDestination($src) {
    $dest = "public://migrated/hubspot" . $this->fileSystem->dirname(parse_url($src, PHP_URL_PATH));
    return $dest;
  }

  /**
   * Return the url of the file located at $uri, if it exists.
   *
   * @param string $uri
   *   The local stream uri of the file.
   *
   * @return string
   *   The URL of the file, or empty string if it doesn't exist.
   */
  protected function loadFile($uri) {
    $url = $this->fileExists($uri) ? file_create_url($uri) : '';
    return $url;
  }

  /**
   * Return an array of file ids that match $uri.
   *
   * @param string $uri
   *   The stream uri.
   *
   * @return int[]
   *   An array of file ids, empty if none match $uri.
   */
  protected function fileExists($uri) {
    return $this->fileStorage->getQuery()->condition('uri', $uri)->execute();
  }

  /**
   * Download the file at $src to the local destination at $dest.
   *
   * @param string $src
   *   The URL of the file to download.
   * @param string $dest
   *   The stream uri destination for the file.
   *
   * @return string
   *   The new public URL of the downloaded file.
   */
  protected function downloadFile($src, $dest) {
    if ($file = $this->retrieveFile($src, $dest)) {
      return $this->cacheFile($src, file_create_url($file->getFileUri()));
    }
    else {
      return $this->cacheFile($src, FALSE);
    }
  }

  /**
   * Retrieve file for caching.
   */
  public function retrieveFile($src, $dest) {
    if (\Drupal::service('file_system')->prepareDirectory($dest, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
      $file = system_retrieve_file($src, $dest, TRUE);
      return $file;
    }
    throw new \Exception("Destination $dest could not be prepared.");
  }

  /**
   * Store the $url of the file downloaded from $src in the cache variable.
   */
  protected function cacheFile($src, $url) {
    $this->files[$src] = $url;
    return $url;
  }

}

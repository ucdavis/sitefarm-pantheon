<?php

namespace Sitefarm\Composer;

use Composer\Script\Event;
use DirectoryIterator;

/**
 * Class Assets for managing asset-packagist.org libraries.
 *
 * Move and rename asset packages pulled in by asset-packagist.org. Drupal
 * requires javascript libraries to be in specifically named folders which
 * may no be what comes from packagist. So they need to be moved to correct
 * directories and renamed.
 *
 * @package Sitefarm\Composer
 */
class Assets {

  /**
   * Path to the Libraries directory.
   *
   * @var string
   */
  public static $libraryPath = 'web/libraries/';

  /**
   * List of Asset Packages.
   *
   * The original directory name is the key and the final destination name is
   * the value.
   *
   * original => destination
   *
   * @var array
   */
  public static $assets = [
    'ckeditor-autosave-plugin' => 'autosave',
    'ckeditor-featureblock' => 'feature_block',
    'ckeditor-indentblock' => 'indentblock',
    'ckeditor-medialink' => 'media_link',
    'ckeditor-notification' => 'notification',
    'ckeditor-wordcount-plugin' => 'wordcount',
    'slick-carousel' => 'slick',
  ];

  /**
   * Move and rename the asset-packagist directory to one recognized by Drupal.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function rename(Event $event) {

    foreach (static::$assets as $original => $destination) {
      $original_path = static::$libraryPath . $original;
      $destination_path = static::$libraryPath . $destination;

      // Check if the original destination is present and exit if it does not.
      if (!is_dir($original_path)) {
        continue;
      }

      // Delete any existing destination directory if it is already there.
      if (is_dir($destination_path)) {
        static::rrmdir($destination_path);
      }

      // Check if the destination directory is currently a subdirectory of the
      // Original. If so then just move it and delete original.
      $subdirectory = $original_path . '/' . $destination;
      if (is_dir($subdirectory)) {
        // Move the subdirectory to the library.
        rename($subdirectory, $destination_path);
        // Delete the original.
        static::rrmdir($original_path);
      }

      // Rename the original to the destination directory.
      else {
        rename($original_path, $destination_path);
      }
    }

  }

  /**
   * Recursively removes a folder along with all its files and directories.
   *
   * @param string $path
   *   The file path used for removal.
   */
  public static function rrmdir($path) {
    // Open the source directory to read in files.
    $iterator = new DirectoryIterator($path);
    foreach ($iterator as $file) {
      if ($file->isFile()) {
        unlink($file->getRealPath());
      }
      else {
        if (!$file->isDot() && $file->isDir()) {
          static::rrmdir($file->getRealPath());
        }
      }
    }
    rmdir($path);
  }

}

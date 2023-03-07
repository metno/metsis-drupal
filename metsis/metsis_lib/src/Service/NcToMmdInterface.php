<?php

namespace Drupal\metsis_lib\Service;

/**
 * Interface NcToMmdInterface.
 *
 * @package Drupal\metsis_lib\Service
 */
interface NcToMmdInterface {

  /**
   * Retrieve metadata from netCDF file.
   *
   * @param string $filepath
   * @param string $filename
   * @param sting $output_path
   *
   * @return array
   */
  public function getMetadata(string $filepath, string $filename, string $output_path): array;

  /**
   * Retrieve metadata extraction status.
   *
   * @return bool
   */
  public function getStatus();

}

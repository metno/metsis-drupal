<?php

namespace Drupal\metsis_lib\Service;

/**
 * Interface for the nc to mmd service.
 *
 * @package Drupal\metsis_lib\Service
 */
interface NcToMmdInterface {

  /**
   * Retrieve metadata from netCDF file.
   *
   * @param string $filepath
   *   The input file path.
   * @param string $filename
   *   The input filename.
   * @param string $output_path
   *   The output filepath.
   *
   * @return array
   *   The extracted information.
   */
  public function getMetadata(string $filepath, string $filename, string $output_path): array;

  /**
   * Retrieve metadata extraction status.
   *
   * @return bool
   *   Return the status of the execution.
   */
  public function getStatus();

}

<?php

namespace Drupal\metsis_lib\Service;

/**
 * Factory for the nc to mmd service.
 *
 * @package Drupal\metsis_lib\Service
 */
class NcToMmdFactory {

  /**
   * Create a new fully prepared instance of NcToMmd.
   *
   * @return \Drupal\metsis_lib\Service\NcToMmd
   *   Returns the service.
   */
  public function create() {

    return new NcToMmd();
  }

}

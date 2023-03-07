<?php

namespace Drupal\metsis_lib\Service;

/**
 * Class NcToMmdFactory.
 *
 * @package Drupal\metsis_lib\Service
 */
class NcToMmdFactory {

  /**
   * Create a new fully prepared instance of NcToMmd.
   *
   * @return \Drupal\metsis_lib\Service\NcToMmd
   */
  public function create() {

    return new NcToMmd();
  }

}

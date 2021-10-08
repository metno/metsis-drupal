<?php

namespace Drupal\metsis_lib\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;

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

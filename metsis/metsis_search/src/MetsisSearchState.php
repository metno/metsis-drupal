<?php

namespace Drupal\metsis_search;

/**
 * Service for storing state for the metsis search during a request response.
 */
class MetsisSearchState {
  /**
   * Store the state data.
   *
   * @var array
   */
  protected $data = [];

  /**
   * Get the state_data value for the given key.
   *
   * If no value is found, return NULL.
   */
  public function get($key) {
    return $this->data[$key] ?? NULL;
  }

  /**
   * Set a value in the state data array.
   */
  public function set($key, $value) {
    $this->data[$key] = $value;
  }

}

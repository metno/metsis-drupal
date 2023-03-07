<?php

namespace Drupal\metsis_basket;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the metsis_basket_basket_item entity type.
 *
 * {@inheritdoc}.
 */
class BasketItemViewsData extends EntityViewsData {

  /**
   * Using standard interface to create views data information.
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    return $data;
  }

}

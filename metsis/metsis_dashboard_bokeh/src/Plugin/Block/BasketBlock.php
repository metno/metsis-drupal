<?php
/*
 * @file
 * Contains \Drupal\metsis_dashboard_bokeh\Plugin\Block\BasketBlock
 *
 * BLock to show basket button and number of items
 *
 */
namespace Drupal\metsis_dashboard_bokeh\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a Block.
 *
 * @Block(
 *   id = "metsis_basket_block_bokeh",
 *   admin_label = @Translation("METSIS Basket Bokeh Block"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class BasketBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   * Add js to block and return renderarray
   */
  public function build() {
    \Drupal::logger('metsis_dashboard_bokeh')->debug("Building Basket Block");

    //Check if we already have an active bboxFilter
    $basket_count = $this->getUserItemCount();


    //Build  render array
    $build['basket-wrapper'] = [
      '#prefix' => '<div id="basket-block" class="basket-block w3-container w3-leftbar">',
      '#postfix' => '</div>'
    ];
    $build['basket-wrapper']['link-button'] = [
     '#markup' => '<a id="myBasket" class="w3-btn basket-link" href="/metsis/bokeh/dashboard">My Basket</a>',
     '#allowed_tags' => ['a'],
    ];
    $build['basket-wrapper']['badge'] = [
      '#markup' => '<span id="myBasketCount" class="w3-badge w3-green">' . $basket_count . '</span>',
      '#allowed_tags' => ['span'],
    ];
    $build['#cache'] = [
      'contexts' => [
        'url.path',
        'url.query_args',
      ],
    ];
    $build['#attached'] = [
      'library' => [
              'metsis_lib/adc_button'
      ],
    ];

  //Return render array
  return $build;
  }

  function getUserItemCount() {

    /**
     * Get count of resources from private tempstore
     */
     $tempstore = \Drupal::service('tempstore.private');
     // Get the store collection.
     $store = $tempstore->get('metsis_dashboard_bokeh');
     $resources = $store->get('basket');

     $count = sizeof($resources);
    return $count;
  }
}

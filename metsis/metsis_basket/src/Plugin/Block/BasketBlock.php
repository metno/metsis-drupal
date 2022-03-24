<?php
/*
 * @file
 * Contains \Drupal\metsis_basket\Plugin\Block\BasketBlock
 *
 * BLock to show basket button and number of items
 *
 */

namespace Drupal\metsis_basket\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metsis_basket\Controller\MetsisBasketController;

/**
 * Provides a Block.
 *
 * @Block(
 *   id = "metsis_basket_block",
 *   admin_label = @Translation("METSIS Basket Block"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class BasketBlock extends BlockBase implements BlockPluginInterface
{
  /**
   * {@inheritdoc}
   * Add js to block and return renderarray
   */
    public function build()
    {
        //\Drupal::logger('metsis_dashboard_bokeh')->debug("Building Basket Block");

        //Check if we already have an active bboxFilter
        $basket_count = $this->getUserItemCount();


        //Build  render array
        $build['basket-wrapper'] = [
      '#prefix' => '<div id="basket-block" class="basket-block w3-container w3-leftbar">',
      '#suffix' => '</div>'
    ];
        $build['basket-wrapper']['link-button'] = [
     '#markup' => '<a id="myBasket" class="w3-btn basket-link" href="/metsis/mybasket">My Basket</a>',
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
        $build['#cache']['max-age'] = 2;
        $build['#attached'] = [
      'library' => [
              'metsis_lib/adc_button'
      ],
    ];

        //Return render array
        return $build;
    }

    public function getUserItemCount()
    {
        $user_id = (int) \Drupal::currentUser()->id();
        return MetsisBasketController::get_user_item_count($user_id);
    }
}

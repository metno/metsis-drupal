<?php

namespace Drupal\metsis_basket\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\metsis_basket\Controller\MetsisBasketController;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Contains \Drupal\metsis_basket\Plugin\Block\BasketBlock.
 *
 * BasketBlock.
 *
 * BLock to show basket button and number of items
 */

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
class BasketBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Construct an example service instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Account proxy for the currently logged-in user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Add js to block and return renderarray.
   */
  public function build() {
    // \Drupal::logger('metsis_dashboard_bokeh')
    // ->debug("Building Basket Block");
    // Check if we already have an active bboxFilter.
    $basket_count = $this->getUserItemCount();

    // Build  render array.
    $build['basket-wrapper'] = [
      '#prefix' => '<div id="basket-block" class="basket-block w3-container w3-leftbar">',
      '#suffix' => '</div>',
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
        'metsis_lib/adc_button',
      ],
    ];

    // Return render array.
    return $build;
  }

  /**
   * Get the user item count frontend.
   */
  public function getUserItemCount() {
    $user_id = (int) $this->currentUser->id();
    return MetsisBasketController::getUserItemCount($user_id);
  }

}

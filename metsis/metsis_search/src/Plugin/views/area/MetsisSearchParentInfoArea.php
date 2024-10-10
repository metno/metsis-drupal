<?php

namespace Drupal\metsis_search\Plugin\views\area;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metsis_search\MetsisSearchState;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an area plugin to display parent information.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("metsis_search_parent_info")
 */
class MetsisSearchParentInfoArea extends AreaPluginBase {

  /**
   * The MetsisSearchState service for holding data between events during request.
   *
   * @var \Drupal\metsis_search\MetsisSearchState
   */
  protected $metsisState;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ParentInfoArea instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\metsis_search\MetsisSearchState $metsis_state
   *   The MetsisSearchState service.
   * @param Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Config Factory..
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MetsisSearchState $metsis_state,
    ConfigFactoryInterface $configFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->metsisState = $metsis_state;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('metsis_search.state'),
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function title() {
    return $this->t('Metsis Search Parent Info Area');
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->metsisState->get('parent_info'));
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    if ($empty && empty($this->options['empty'])) {
      return [];
    }

    $parent_info = $this->metsisState->get('parent_info');
    if (!empty($parent_info)) {

      return [
        '#theme' => 'metsis_search_parent_info',
        '#data' => $parent_info,
      ];
    }
    else {
      return [];
    }
  }

}

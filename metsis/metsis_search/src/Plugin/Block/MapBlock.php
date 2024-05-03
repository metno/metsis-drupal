<?php

namespace Drupal\metsis_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\UncacheableDependencyTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a Block.
 *
 * @Block(
 *   id = "metsis_search_map_block",
 *   admin_label = @Translation("Map Block for METSIS Search"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class MapBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {
  use UncacheableDependencyTrait;

  /**
   * The injected module_handler service.
   *
   * @var \ Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The container create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container dependency injection interface.
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('config.factory')
    );
  }

  /**
   * The class constructor with dependency injection.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The Plugin definition.
   * @param \Drupal\Core\Extension\ModuleHandler $moduleHandler
   *   The module_handler service.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request_stack service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ModuleHandler $moduleHandler,
    Request $request,
    ConfigFactoryInterface $configFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $moduleHandler;
    $this->request = $request;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   *
   * Add js to block and return renderarray.
   */
  public function build() {
    // \Drupal::logger('metsis_search')->debug("Building MapSearchForm");
    // Get the module path.
    $module_path = $this->moduleHandler->getModule('metsis_search')->getPath();
    // Get the bounding box drawn on the map
    // \Drupal::logger('metsis_search')->debug("Building MapSearchForm");
    // $tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
    // $bboxFilter = $tempstore->get('bboxFilter');.
    $session = $this->request->getSession();
    // $bboxFilter = $session->get('bboxFilter');
    $tllat = "";
    $tllon = "";
    $brlat = "";
    $brlon = "";
    $proj = $session->get('proj');
    /*
    if ($bboxFilter != null) {
    $tllat = $session->get('tllat');
    $tllon = $session->get('tllon');
    $brlat = $session->get('brlat');
    $brlon = $session->get('brlon');
    }
     */
    // Get saved configuration.
    $config = $this->configFactory->get('metsis_search.settings');
    $map_location = $config->get('map_selected_location');
    $map_lat = $config->get('map_locations')[$map_location]['lat'];
    $map_lon = $config->get('map_locations')[$map_location]['lon'];
    $map_zoom = $config->get('map_zoom');
    $map_additional_layers = $config->get('map_additional_layers_b');
    $map_projections = $config->get('map_projections');
    $map_init_proj = $config->get('map_init_proj');
    $map_search_text = $config->get('map_search_text');
    $map_base_layer_wms_north = $config->get('map_base_layer_wms_north');
    $map_base_layer_wms_south = $config->get('map_base_layer_wms_south');
    $map_search_text = $config->get('map_search_text');
    $map_layers_list = $config->get('map_layers');
    // Get the extracted info from tempstore
    // $tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
    // $extracted_info = $session->get('extracted_info');.
    if ($proj != NULL) {
      $map_init_proj = $proj;
    }

    $build['search-map'] = [
      '#prefix' => '<p><div data-map-search id="map-search" class="w3-border map-search">',
      '#suffix' => '</div>',
    ];
    // search-map wrapper.
    $build['search-map']['panel'] = [
      '#prefix' => '<div id="panel" class="panel">',
      '#markup' => $map_search_text,
      '#suffix' => '</div><br>',
    ];
    $build['search-map']['panel']['layers'] = [
      '#type' => 'markup',
      '#markup' => '<div class="layers-wrapper"></div>',
      '#allowed_tags' => ['div', 'label'],
    ];
    // Message to be displayed under the map.
    $build['search-map']['projection'] = [
      '#type' => 'markup',
      '#markup' => '<div class="proj-wrapper"><label class="proj-label"><strong>Select Projection</strong></label><br></div>',
      '#allowed_tags' => ['div', 'label'],
    ];

    $build['map-div'] = [
      '#type' => 'markup',
      '#markup' => '<div id="metmap" class="metmap"></div>',
      '#allowed_tags' => ['div', 'label'],
      '#cache' => ['max-age' => 0],
    ];
    $build['suffix'] = [
      '#markup' => '<br><br>',
    ];

    // $build['#cache'] = [
    // 'max-age' => 0,
    // ];
    $build['#attached'] = [
      'library' => [
        'metsis_search/search_map',
      ],
      'drupalSettings' => [
        'metsis_search' => [
      // To be replaced with configuration variables.
          'mapLat' => $map_lat,
      // To be replaced with configuration variables.
          'mapLon' => $map_lon,
      // To be replaced with configuration variables.
          'mapZoom' => $map_zoom,
      // To be replaced with configuration variables.
          'init_proj' => $map_init_proj,
          'additional_layers' => $map_additional_layers,
          'base_layer_wms_north' => $map_base_layer_wms_north,
          'base_layer_wms_south' => $map_base_layer_wms_south,
          'projections' => $map_projections,
          'layers_list' => $map_layers_list,
          'tllat' => $tllat,
          'tllon' => $tllon,
          'brlon' => $brlon,
          'brlat' => $brlat,
          'proj' => $proj,
          'module_path' => $module_path,
        ],
      ],

    ];
    // Return render array.
    return $build;
  }

}

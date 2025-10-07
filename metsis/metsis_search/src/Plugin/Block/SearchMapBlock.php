<?php

namespace Drupal\metsis_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\metsis_search\MetsisSearchState;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\RendererInterface;

/**
 * Provides a Block.
 *
 * @Block(
 *   id = "metsis_search_map",
 *   admin_label = @Translation("METSIS Search Map"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class SearchMapBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {
  // Use UncacheableDependencyTrait;.
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
   * MetsisSearchState service for holding data between events during request.
   *
   * @var array
   */
  protected $metsisState;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
      $container->get('config.factory'),
      $container->get('metsis_search.state'),
      $container->get('renderer')
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
   * @param \Drupal\metsis_search\MetsisSearchState $state
   *   The metsisSearch state service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ModuleHandler $moduleHandler,
    Request $request,
    ConfigFactoryInterface $configFactory,
    MetsisSearchState $state,
    RendererInterface $renderer,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $moduleHandler;
    $this->request = $request;
    $this->configFactory = $configFactory;
    $this->metsisState = $state;
    $this->renderer = $renderer;
  }

  /**
   * See {@inheritdoc}.
   */
  public function build() {
    // Get the module path.
    $module_path = $this->moduleHandler->getModule('metsis_search')->getPath();
    // Get the bounding box drawn on the map.
    // $session = $this->request->getSession();
    // $bboxFilter = $session->get('bboxFilter');
    // $proj = $session->get('proj');
    // Extract info from request object:
    $searchUri = $this->request->getRequestUri();
    // $tllat = $session->get('tllat');
    // $tllon = $session->get('tllon');
    // $brlat = $session->get('brlat');
    // $brlon = $session->get('brlon');
    // $filter = $session->get('cond');
    $queryArgs = $this->request->query->all();

    // Get saved configuration.
    $config = $this->configFactory->get('metsis_search.settings');
    $bbox_filter_auto_show = $config->get('hide_bbox_filter_exposed');
    $map_location = $config->get('map_selected_location');
    $map_lat = $config->get('map_locations')[$map_location]['lat'];
    $map_lon = $config->get('map_locations')[$map_location]['lon'];
    $map_zoom = $config->get('map_zoom');
    $map_additional_layers = $config->get('map_additional_layers_b');
    $map_projections = $config->get('map_projections');
    $map_init_proj = $config->get('map_init_proj');
    $map_base_layer_wms_north = $config->get('map_base_layer_wms_north');
    $map_base_layer_wms_south = $config->get('map_base_layer_wms_south');
    $map_layers_list = $config->get('map_layers');
    $map_pins = $config->get('map_pins_b');
    $map_filter = $config->get('map_bbox_filter');
    $pywps_service = $config->get('pywps_service');
    if (NULL != $config->get('map_wms_layers_skip')) {
      $map_wms_layers_skip = explode(',', $config->get('map_wms_layers_skip'));
    }
    else {
      $map_wms_layers_skip = [];
    }
    /* Get the extracted info from session. */
    // $extracted_info = $this->metsisState->get('extracted_info');
    // If ($session->get("place_filter") != NULL) {
    // $map_filter = $session->get("place_filter");
    // }.
    /*
     * Create the render array
     */

    // search-map wrapper.
    $build['search-map'] = [
      '#prefix' => '<div id="search-map" class="search-map w3-card-2 clearfix">',
      '#suffix' => '</div>',
    ];

    $build['search-map']['top-panel'] = [
      '#prefix' => '<div id="map-top-panel" class="map-top-panel w3-container">',
      '#suffix' => '</div>',
    ];

    // Top panel projection selection markup.
    $build['search-map']['top-panel']['projection'] = [
      '#type' => 'markup',
      '#markup' => '<div class="proj-wrapper"><label class="proj-label"><strong>Select projection:</strong></label></div>',
      '#allowed_tags' => ['div', 'label', 'strong'],
    ];

    $build['search-map']['top-panel']['map-filter'] = [
      '#type' => 'markup',
      '#markup' => '<div class="map-filter-wrapper"><label class="map-filter-label"><strong>Select spatial filter:</strong></label></div>',
      '#allowed_tags' => ['div', 'label', 'strong'],
    ];

    // Top Panel button container and buttons markup.
    $build['search-map']['top-panel']['buttons-container'] = [
      '#prefix' => '<div id="buttonsContainer" class="buttons-wrapper">',
      '#suffix' => '</div>',
    ];
    $build['search-map']['top-panel']['buttons-container']['bbox-filter'] = [
      '#type' => 'markup',
      '#markup' => '<span><button id="bboxButton" class="w3-left adc-button adc-sbutton">Create bounding box filter</button></span>',
      '#allowed_tags' => ['div', 'label', 'button', 'br', 'span'],
    ];

    $build['search-map']['top-panel']['buttons-container']['vis-all'] = [
      '#type' => 'markup',
      '#markup' => '<span id="vizClass"><button id="vizAllButton" class="w3-center adc-button adc-sbutton"></button></span>',
      '#allowed_tags' => ['div', 'label', 'button', 'br', 'span'],
    ];
    $build['search-map']['top-panel']['buttons-container']['reset'] = [
      '#type' => 'markup',
      '#markup' => '<span id="resetButtonID"><a id="resetButton" href="/metsis/search/reset" class="w3-center adc-button adc-sfaddbutton">Reset search</a></span>',
      '#allowed_tags' => ['div', 'label', 'button', 'br', 'a', 'span', 'input'],
    ];
    $build['search-map']['top-panel']['buttons-container']['reset-map'] = [
      '#type' => 'markup',
      '#markup' => '<span id="resetMapButtonID"><button id="resetMapButton" class="w3-center adc-button adc-sbutton" >Reset map</button></span>',
      '#allowed_tags' => ['div', 'label', 'button', 'br', 'a', 'span'],
    ];
    $build['search-map']['top-panel']['buttons-container']['loader'] = [
      '#type' => 'markup',
      '#markup' => '<span id="mapLoaderSpan"><img id="mapLoader"/></span>',
      '#allowed_tags' => ['div', 'label', 'button', 'br', 'a', 'span', 'img'],
    ];

    // Top panel current bbox filter text markup.
    $build['search-map']['top-panel']['filter'] = [
      '#type' => 'markup',
      '#markup' => '<span class="current-bbox-filter-label"></span><span class="current-bbox-filter"></span> <span class="current-bbox-select"></span><span class="remove-bbox-filter"></span>',
      '#allowed_tags' => ['span', 'label', 'button', 'br', 'hr'],
    ];

    // Placeholder for additional layers select list.
    // $build['search-map']['top-panel']['layers'] = [
    // '#type' => 'markup',
    // '#markup' => '<div class="layers-wrapper"></div>',
    // '#allowed_tags' => ['div', 'label'],
    // ];.
    /*
     * Openlayers map viewport container
     */
    $build['search-map']['map-fullscreen-wrapper'] = [
      '#prefix' => '<div id="mapcontainer" class="mapcontainer">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div'],
    ];
    $build['search-map']['map-fullscreen-wrapper']['map'] = [
      '#type' => 'markup',
      '#markup' => '<div data-map-res id="map-res" class="map-res">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div'],
    ];

    // Side panel collapseable.
    $build['search-map']['map-fullscreen-wrapper']['side-panel'] = [
      '#prefix' => '<div id="map-sidepanel" class="map-sidepanel">',
      '#markup' => '<span class="map-closebtn-wrapper"></span><span class="map-sidepanel-title">Side Panel</span>',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div', 'span', 'a', 'button'],
    ];

    // Wms legend placeholder.
    $build['search-map']['map-fullscreen-wrapper']['side-panel']['legend'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="w3-container w3-margin-left legend-placeholder">',
      '#markup' => '<img id="map-wms-legend" src="/core/misc/throbber-active.gif"/>',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div', 'img'],

    ];

    // Wms Styles Dropdown.
    $build['search-map']['map-fullscreen-wrapper']['side-panel']['wms-styles'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="w3-container w3-margin-left wmsstyle-placeholder">',
      '#markup' => '<div id="wms-style-id" class="wms-style-dropdown></div>',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div', 'img'],

    ];

    // Placeholder for the ol-ext layerswitcher inside side-panel.
    $build['search-map']['map-fullscreen-wrapper']['side-panel']['layerswitcher'] = [
      '#markup' => '<div class="external layerSwitcher"><b>Layer switcher</b></div>',
      '#allowed_tags' => ['div', 'b'],
    ];

    // Bottom -panel.
    $build['search-map']['map-fullscreen-wrapper']['bottom-panel'] = [
      '#type' => 'markup',
      '#markup' => '<div id="bottomMapPanel" class="bottom-map-panel">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div'],
    ];
    // Progress bar wrapper.
    $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['progress-bar'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="progress-container">',
      '#markup' => '<div id="progress"></div>',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div', 'span', 'i', 'button'],
    ];

    // Date controls wrapper.
    $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['animated-controls'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="animatedWmsControls">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div', 'span', 'i', 'button'],

    ];
    // Timeslider wrapper.
    $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['animated-controls']['time-slider'] = [
      '#type' => 'markup',
      '#markup' => '<div id="map-timeslider-id"><div class="ui-slider-handle"></div></div>',
    ];

    // Timeslider wrapper.
    $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['animated-controls']['time-controls'] = [
      '#type' => 'markup',
      '#markup' => '<div class="timeControlWrapper controls"><span>Time dimensions: </span><button id="timeBack" class="timeButton"><i class="fas fa-angle-double-left"></i></button><span id="time">11.11.2022</span><button id="timeForward" class="timeButton"><i class="fas fa-angle-double-right"></i></button></div>',
      '#allowed_tags' => ['div', 'span', 'i', 'button'],

    ];

    // Date controls wrapper elevation.
    $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['elevation-controls'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="elevationWmsControls">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div', 'span', 'i', 'button'],
    ];
    // Timeslider wrapper elevation.
    $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['elevation-controls']['elevation-buttons'] = [
      '#type' => 'markup',
      '#markup' => '<div class="elevationControlWrapper controls"><span>Elevation dimensions: </span> <button id="elevationUp" class="elevationButton"><i class="fas fa-angle-double-up"></i></button><span id="elevation" data-current=0>0</span><button id="elevationDown" class="elevationButton"><i class="fas fa-angle-double-down"></i></button></div>',
      '#allowed_tags' => ['div', 'span', 'i', 'button'],
    ];
    // Define popup markup.
    $build['search-map']['popup'] = [
      '#prefix' => '<div id="popup" class="ol-popup" title="Select product:">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div'],
    ];
    $build['search-map']['popup']['closer'] = [
      '#type' => 'markup',
      '#markup' => '<a href="#" id="popup-closer" class="ol-popup-closer"></a>',
      '#allowed_tags' => ['a'],
    ];
    $build['search-map']['popup']['content'] = [
      '#type' => 'markup',
      '#markup' => '<div id="popup-content" class="popup-content w3-small"></div>',
      '#allowed_tags' => ['div'],
    ];
    // Placeholder for ts-plot.
    $build['map-ts-plot'] = [
      '#prefix' => '<div id="bokeh-map-ts-plot" style="width:auto;height:auto">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div'],
    ];

    $build['map-ts-plot']['header'] = [
      '#type' => 'markup',
      '#markup' => '<div class="map-ts-header"><span class="w3-center"><h3>Visualize timeseries</h3></span></div>',
      '#allowed_tags' => ['div', 'h', 'h3', 'span'],
    ];

    $build['map-ts-plot']['loader'] = [
      '#type' => 'markup',
      '#markup' => '<div class="map-ts-loader"></div>',
      '#allowed_tags' => ['div'],
    ];
    $build['map-ts-plot']['back'] = [
      '#type' => 'markup',
      '#markup' => '<div id="map-ts-back" class="map-ts-back"></div>',
      '#allowed_tags' => ['div'],
    ];
    $build['map-ts-plot']['variables'] = [
      '#type' => 'markup',
      '#markup' => '<div class="map-ts-vars"></div>',
      '#allowed_tags' => ['div'],
    ];
    $build['map-ts-plot']['plot'] = [
      '#type' => 'markup',
      '#markup' => '<div id="map-ts-plot" name="tsplot" class="map-ts-plot"></div>',
      '#allowed_tags' => ['div'],
    ];

    // Add CSS and JS libraries and drupalSettings JS variables.
    $build['#attached'] = [
      'library' => [
        'metsis_lib/adc-button',
        'metsis_search/search_map_block',
      ],
    ];

    // Cache settings for the block.
    $build['#cache'] = [
      'max-age' => Cache::PERMANENT,
      'contexts' => ['url.query_args'],
      'tags' => ['metsis_search_map'],
    ];
    $settings = [
      'metsis_search_map_block' => [
        'mapLat' => $map_lat,
        'mapLon' => $map_lon,
        'mapZoom' => $map_zoom,
        'init_proj' => $map_init_proj,
        'additional_layers' => $map_additional_layers,
        'base_layer_wms_north' => $map_base_layer_wms_north,
        'base_layer_wms_south' => $map_base_layer_wms_south,
        'projections' => $map_projections,
        'layers_list' => $map_layers_list,
          // 'tllat' => $tllat,
          // 'tllon' => $tllon,
          // 'brlon' => $brlon,
          // 'brlat' => $brlat,
          // 'proj' => $proj,
          // 'cond' => $filter,
      // 'bboxFilter' => $bboxFilter,
        'mapFilter' => $map_filter,
        'pins' => $map_pins,
        'path' => $module_path,
        'extracted_info' => $this->metsisState->get('extracted_info'),
        'pywps_service' => $pywps_service,
        'current_search' => $searchUri,
        'wms_layers_skip' => $map_wms_layers_skip,
        'bbox_filter' => $this->metsisState->get('bbox_filter'),
        'bbox_op' => $this->metsisState->get('bbox_op'),
        'bbox_filter_auto_show' => $bbox_filter_auto_show,
        'query_args' => $queryArgs,
      ],
    ];
    // Placeholder for dynamic drupalSettings.
    $build['#attached']['drupalSettings'] = $settings;
    $this->metsisState->set('extracted_info', []);
    $this->renderer->addCacheableDependency($build, $config);

    return $build;
  }

  /**
   * Get the max age caching for this block.
   */
  /* public function getCacheMaxAge() {
  return 1;
  }*/

  /**
   * Return an empty tilte.
   */
  public function getTitle() {
    return '';
  }

}

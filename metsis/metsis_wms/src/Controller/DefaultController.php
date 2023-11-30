<?php

namespace Drupal\metsis_wms\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\search_api\Entity\Index;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default controller for the metsis_wms module.
 */
class DefaultController extends ControllerBase {

  /**
   * The injected module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * WMS Controller constructor.
   *
   * @param Drupal\Core\Extension\ModuleHandler $module_handler
   *   The form builder.
   */
  public function __construct(ModuleHandler $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('module_handler')
      );
  }

  /**
   * Get the custom content.
   */
  public function getCustomContent() {
    $datasetURL = filter_input(INPUT_GET, "datasetURL");
    // var_dump($datasetURL);
    $content = [
      '#markup' => '<div class="map container"><div id="map"></div><div id="lyr-switcher"></div>' . '<div id="proj-container"></div><div id="timeslider-container"></div></div>' . '<div id="wmsURL" class="element-hidden">' . $datasetURL . '</div>',
    ];
    return $content;
  }

  /**
   * Get the wms map.
   */
  public function getWmsMap(Request $request) {
    $query_from_request = $request->query->all();
    $query = UrlHelper::filterQueryParameters($query_from_request);
    $referer = $request->headers->get('referer', '/metsis/search');
    // dpm($referer);

    $module_path = $this->moduleHandler->getModule('metsis_search')->getPath();
    // dpm($query);
    $ds_query = $query['dataset'] ?? NULL;
    $url_query = $query['wms_url'] ?? NULL;
    // dpm($ds_query);
    // dpm($url_query);
    // Redirect back to search with message if no info are given.
    if ($ds_query == NULL && $url_query == NULL) {
      $this->messenger()->addStatus($this->t("Missing dataset or url query parameter, or valid dataset id"));
      return new RedirectResponse('/metsis/search');
    }
    /*
     * Variables from configuration
     */
    // Get saved configuration.
    $config = $this->config('metsis_search.settings');
    $map_location = $config->get('map_selected_location');
    $map_lat = $config->get('map_locations')[$map_location]['lat'];
    $map_lon = $config->get('map_locations')[$map_location]['lon'];
    $map_zoom = $config->get('map_zoom');
    $map_additional_layers = $config->get('map_additional_layers_b');
    $map_projections = $config->get('map_projections');
    $map_init_proj = $config->get('map_init_proj');
    $map_layers_list = $config->get('map_layers');
    $pywps_service = $config->get('pywps_service');
    $map_wms_layers_skip = explode(',', $config->get('map_wms_layers_skip'));

    $session = $request->getSession();
    $proj = $session->get('proj');

    if (count($query) > 0) {
      if ($ds_query != NULL) {
        $datasets = explode(",", $query['dataset']);
        $fields = [
          "id",
          "title",
          "data_access_url_ogc_wms",
          "data_access_wms_layers",
          "metadata_identifier",
          'geographic_extent_rectangle_north',
          'geographic_extent_rectangle_south',
          'geographic_extent_rectangle_east',
          'geographic_extent_rectangle_west',
        ];
        /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
        $index = Index::load('metsis');

        /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
        $backend = $index->getServerInstance()->getBackend();

        $connector = $backend->getSolrConnector();

        $solarium_query = $connector->getSelectQuery();

        $ids = implode(' ', $datasets);
        $solarium_query->setQuery('id:(' . $ids . ')');
        // dpm($solarium_query->getQuery());
        // }
        // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
        // $solarium_query->setRows(2);.
        $solarium_query->setFields($fields);

        $result = $connector->execute($solarium_query);

        // \Drupal::logger('metsis_wms')->debug("found :" .$found);
        $wms_data = [];
        foreach ($result as $document) {
          $fields = $document->getFields();
          if (isset($fields['data_access_url_ogc_wms'])) {
            $mi = $fields['metadata_identifier'];

            foreach ($fields['data_access_url_ogc_wms'] as $wms_url) {
              $wms_data[$mi]['dar'][] = $wms_url;
            }

            if (isset($fields['data_access_wms_layers'])) {
              foreach ($fields['data_access_wms_layers'] as $wms_layer) {
                $wms_data[$mi]['layers'][] = $wms_layer;
              }
            }
            $geographical_extent_north = $fields['geographic_extent_rectangle_north'];
            $geographical_extent_south = $fields['geographic_extent_rectangle_south'];
            $geographical_extent_east = $fields['geographic_extent_rectangle_east'];
            $geographical_extent_west = $fields['geographic_extent_rectangle_west'];
            $geographical_extent = [
              $geographical_extent_north,
              $geographical_extent_south,
              $geographical_extent_east,
              $geographical_extent_west,
            ];
            $wms_data[$mi]['geom'] = $geographical_extent;
            $wms_data[$mi]['title'] = $fields['title'];
          }
          else {
            $this->messenger()->addError($this->t("Selected datasets does not contain any WMS resource.<br> Visualization not possible"));
            return new RedirectResponse($referer);
          }
        }
      }
      if ($url_query != NULL) {
        $wms_data = [
          'ext' => [
            'title' => 'Custom WMS url',
            'dar' => [$url_query],
          ],
        ];
      }
      // dpm($wms_data);
      /*
       * Create the render array
       */

      // search-map wrapper.
      $build['search-map'] = [
        '#prefix' => '<div data-search-map id="search-map" class="search-map w3-card-2 clearfix">',
        '#suffix' => '</div>',
      ];

      $build['search-map']['top-panel'] = [
        '#prefix' => '<div id="map-top-panel" class="map-top-panel w3-container">',
        '#suffix' => '</div>',
      ];
      // Top panel projection selection markup.
      $build['search-map']['top-panel']['projection'] = [
        '#type' => 'markup',
        '#markup' => '<div class="proj-wrapper"><label class="proj-label"><strong>Select Projection:</strong></label></div>',
        '#allowed_tags' => ['div', 'label', 'strong'],
      ];

      // Top Panel button container and buttons markup.
      $build['search-map']['top-panel']['buttons-container'] = [
        '#prefix' => '<div id="buttonsContainer" class="buttons-wrapper">',
        '#suffix' => '</div>',
      ];
      $build['search-map']['top-panel']['buttons-container']['go-back'] = [
        '#type' => 'markup',
        '#markup' => '<span id="goBackID"><a id="goBackMapButton" class="w3-center adc-button adc-sbutton" onclick="go_back()">Go back to search</a></span>',
        '#allowed_tags' => ['div', 'label', 'button', 'br', 'a', 'span'],
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

      // Placeholder for additional layers select list.
      /*
       * Openlayers map viewport container
       */
      $build['search-map']['map-fullscreen-wrapper'] = [
        '#prefix' => '<div id="mapcontainer" class="mapcontainer">',
        '#suffix' => '</div>',
        '#allowed_tags' => ['div'],
      ];
      $build['search-map']['map-fullscreen-wrapper']['layers'] = [
        '#type' => 'markup',
        '#markup' => '<div class="layers-wrapper"></div>',
        '#allowed_tags' => ['div', 'label'],
      ];

      $build['search-map']['map-fullscreen-wrapper']['map'] = [
        '#type' => 'markup',
        '#markup' => '<div id="map-res" class="map-res">',
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
        '#markup' => '<span class="map-sidepanel-title">Legend</span><img id="map-wms-legend"/>',
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

      // Date controls wrapper.
      $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['elevation-controls'] = [
        '#type' => 'markup',
        '#prefix' => '<div id="elevationWmsControls">',
        '#suffix' => '</div>',
        '#allowed_tags' => ['div', 'span', 'i', 'button'],

      ];
      // Timeslider wrapper.
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

      // Set the cache for this form.
      $build['#cache'] = [
        'max-age' => 0,
      // 'tags' =>$this->getCacheTags(),
        'contexts' => [
      // 'route',
          'url.path',
          'url.query_args',
        ],
      ];

      // Add CSS and JS libraries and drupalSettings JS variables.
      $build['#attached'] = [
        'library' => [
          'metsis_lib/adc_buttons',
          'metsis_lib/go_back',
          'metsis_wms/wms_ol6',
        ],
        'drupalSettings' => [
          'metsis_wms_map' => [
        // To be replaced with configuration variables.
            'mapLat' => $map_lat,
        // To be replaced with configuration variables.
            'mapLon' => $map_lon,
        // To be replaced with configuration variables.
            'mapZoom' => $map_zoom,
        // To be replaced with configuration variables.
            'init_proj' => $map_init_proj,
        // To be replaced with configuration variables.
            'additional_layers' => $map_additional_layers,
            'projections' => $map_projections,
            'layers_list' => $map_layers_list,
            'path' => $module_path,
            'pywps_service' => $pywps_service,
            'wms_layers_skip' => $map_wms_layers_skip,
            'wms_data' => $wms_data,
            'selected_proj' => $proj,
          ],
        ],
      ];

      // Set the id of the form.
      /* $build['#attributes'] = [
      'id' => 'map-search',
      ];
       */
      // dpm($wms_data);
      return $build;
    }
  }

}

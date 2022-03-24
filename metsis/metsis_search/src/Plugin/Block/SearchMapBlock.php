<?php
/*
 * @file
 * Contains \Drupal\metsis_search\Plugin\Block\SearchMapBlock
 *
 * BLock to show search map
 *
 */

namespace Drupal\metsis_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

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
class SearchMapBlock extends BlockBase implements BlockPluginInterface
{
    /**
     * {@inheritdoc}
     * Add js to block and return renderarray
     */
    public function build()
    {
        // Get the module path
        $module_handler = \Drupal::service('module_handler');
        $module_path = $module_handler->getModule('metsis_search')->getPath();
        // Get the bounding box drawn on the map
        //\Drupal::logger('metsis_search')->debug("Building MapSearchBlock");
        //$tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
        //$bboxFilter = $tempstore->get('bboxFilter');
        $session = \Drupal::request()->getSession();
        $bboxFilter = $session->get('bboxFilter');
        $proj = $session->get('proj');
        //\Drupal::logger('metsis_search:metsis_search_map')->debug('current session projection: ' . $proj);
        //Extract info from request object:
        $request = \Drupal::request();
        $searchUri = $request->getRequestUri();
        //\Drupal::logger('metsis_search:metsis_search_map')->debug('Current search uri: @url', ['@url' => $searchUri]);

        //if ($bboxFilter != null) {
        $tllat = $session->get('tllat');
        $tllon = $session->get('tllon');
        $brlat = $session->get('brlat');
        $brlon = $session->get('brlon');
        /*
        $tllat = $tempstore->get('tllat');
        $tllon = $tempstore->get('tllon');
        $brlat = $tempstore->get('brlat');
        $brlon = $tempstore->get('brlon'); */
        //\Drupal::logger('metsis_search_map_block')->debug("Got input filter vars: " .$tllat .','. $tllon .','.$brlat.','.$brlon);
        //}

        //Get saved configuration
        $config = \Drupal::config('metsis_search.settings');
        $map_location = $config->get('map_selected_location');
        $map_lat =  $config->get('map_locations')[$map_location]['lat'];
        $map_lon = $config->get('map_locations')[$map_location]['lon'];
        $map_zoom = $config->get('map_zoom');
        $map_additional_layers = $config->get('map_additional_layers_b');
        $map_projections = $config->get('map_projections');
        $map_init_proj =  $config->get('map_init_proj');
        $map_search_text =  $config->get('map_search_text');
        $map_base_layer_wms_north =  $config->get('map_base_layer_wms_north');
        $map_base_layer_wms_south =  $config->get('map_base_layer_wms_south');
        $map_search_text =  $config->get('map_search_text');
        $map_layers_list =  $config->get('map_layers');
        $map_pins = $config->get('map_pins_b');
        $map_filter = $config->get('map_bbox_filter');
        $pywps_service = $config->get('pywps_service');
        if (null != $config->get('map_wms_layers_skip')) {
            $map_wms_layers_skip = explode(',', $config->get('map_wms_layers_skip'));
        } else {
            $map_wms_layers_skip = [];
        }
        //Get the extracted info from tempstore
        //$tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
        $extracted_info = $session->get('extracted_info');

        if ($session->get("place_filter") != null) {
            $map_filter = $session->get("place_filter");
        }
        //dpm($proj);
        //if($proj === null) { $map_init_proj = $proj; }
        /**
         * Create the render array
         */

        // search-map wrapper
        $build['search-map'] = [
          '#prefix' => '<div id="search-map" class="search-map w3-card-2 clearfix">',
          '#suffix' => '</div>'
        ];


        $build['search-map']['top-panel'] = [
          '#prefix' => '<div id="map-top-panel" class="map-top-panel w3-container">',
          '#suffix' => '</div>'
        ];
        /*
        $build['search-map']['panel']['basemap'] = [
          '#type' => 'markup',
          '#markup' => '<div class="basemap-wrapper"><label class="basemap-label"><strong>Select Basemap:</strong></label></div>',
          '#allowed_tags' => ['div','label','strong'],
        ];
        */
        //Top panel projection selection markup
        $build['search-map']['top-panel']['projection'] = [
          '#type' => 'markup',
          '#markup' => '<div class="proj-wrapper"><label class="proj-label"><strong>Select Projection:</strong></label></div>',
          '#allowed_tags' => ['div','label','strong'],
        ];

        //Top Panel button container and buttons markup
        $build['search-map']['top-panel']['buttons-container'] = [
          '#prefix' => '<div id="buttonsContainer" class="buttons-wrapper">',
          '#suffix' => '</div>'
        ];
        $build['search-map']['top-panel']['buttons-container']['bbox-filter'] = [
          '#type' => 'markup',
          '#markup' => '<span><button id="bboxButton" class="w3-left adc-button adc-sbutton">Create bounding box filter</button></span>',
          '#allowed_tags' => ['div','label','button','br','span'],
        ];

        $build['search-map']['top-panel']['buttons-container']['vis-all'] = [
          '#type' => 'markup',
          '#markup' => '<span id="vizClass"><button id="vizAllButton" class="w3-center adc-button adc-sbutton"></button></span>',
          '#allowed_tags' => ['div','label','button','br','span'],
        ];
        $build['search-map']['top-panel']['buttons-container']['reset'] = [
          '#type' => 'markup',
          '#markup' => '<span id="resetButtonID"><a id="resetButton" href="/metsis/search/reset" class="w3-center adc-button adc-sbutton">Reset search</a></span>',
          //'#markup' => '<input data-drupal-selector="edit-reset" type="submit" id="edit-reset--2" name="op" value="Reset" class="button js-form-submit form-submit adc-button adc-sbutton w3-border w3-theme-border w3-margin-top w3-margin-bottom">',
          '#allowed_tags' => ['div','label','button','br','a', 'span', 'input'],
        ];
        $build['search-map']['top-panel']['buttons-container']['reset-map'] = [
          '#type' => 'markup',
          '#markup' => '<span id="resetMapButtonID"><button id="resetMapButton" class="w3-center adc-button adc-sbutton" >Reset map</button></span>',
          '#allowed_tags' => ['div','label','button','br','a', 'span'],
        ];
        $build['search-map']['top-panel']['buttons-container']['loader'] = [
          '#type' => 'markup',
          '#markup' => '<span id="mapLoaderSpan"><img id="mapLoader"/></span>',
          '#allowed_tags' => ['div','label','button','br','a', 'span','img'],
        ];

        //Top panel current bbox filter text markup
        $build['search-map']['top-panel']['filter'] = [
          '#type' => 'markup',
          '#markup' => '<span class="current-bbox-filter"></span> <span class="current-bbox-select"></span>',
          '#allowed_tags' => ['span','label','button','br','hr'],
        ];
        /*  $build['search-map']['top-panel']['opacity'] = [
        '#type' => 'markup',
        '#markup' => '<span class="w3-right">Opacity WMS Layers<div id="map-slider-id" class="w3-right"><div class="ui-slider-handle"></div></div></span>',
        '#allowed_tags' => ['div', 'span'],
        ];
        */

        //Placeholder for additional layers select list
        $build['search-map']['top-panel']['layers'] = [
          '#type' => 'markup',
          '#markup' => '<div class="layers-wrapper"></div>',
          '#allowed_tags' => ['div','label'],
        ];


        /**
         * Openlayers map viewport container
         */
        $build['search-map']['map-fullscreen-wrapper'] = [
           '#prefix' => '<div id="mapcontainer" class="mapcontainer">',
           '#suffix' => '</div>',
           '#allowed_tags' => ['div'],
         ];
        $build['search-map']['map-fullscreen-wrapper']['map'] = [
          //'#prefix' => '<div id="mapcontainer" class="w3-border map-container clearfix">',
          '#type' => 'markup',
          '#markup' => '<div id="map-res" class="map-res">',
          '#suffix' => '</div>',
          '#allowed_tags' => ['div'],
        ];

        //toggle sidebare/layerswitcher button control inside map
        /*        $build['search-map']['map-fullscreen-wrapper']['map']['toggle-sidebar'] = [
                  '#type' => 'markup',
                  '#markup' => '<div class="map-openbtn-wrapper ol-control ol-unselectable"></div>',
                  //'#suffix' => '</div>',
                  '#allowed_tags' => ['div', 'button', 'span'],
                ];
        */

        //Side panel collapseable
        $build['search-map']['map-fullscreen-wrapper']['side-panel'] = [
          '#prefix' => '<div id="map-sidepanel" class="map-sidepanel">',
          '#markup' => '<span class="map-closebtn-wrapper"></span><span class="map-sidepanel-title">Side Panel</span>',
          '#suffix' => '</div>',
          '#allowed_tags' => ['div', 'span', 'a', 'button'],
        ];

        //Date controls wrapper
        /*         $build['search-map']['map-fullscreen-wrapper']['side-panel']['animated-controls'] = [
                    '#type' => 'markup',
                    '#prefix' => '<div id="animatedWmsControls">',
                    '#suffix' => '</div>',
                    '#allowed_tags' => ['div','span', 'i', 'button'],

                  ];
        */
        //Timeslider wrapper
        /*          $build['search-map']['map-fullscreen-wrapper']['side-panel']['animated-controls']['time-slider'] = [
                     '#type' => 'markup',
                     '#markup' => '<div id="map-timeslider-side-id"><div class="ui-slider-handle"></div></div>',
                   ];
        */
        //Timeslider wrapper
        /*           $build['search-map']['map-fullscreen-wrapper']['side-panel']['animated-controls']['time-controls'] = [
                      '#type' => 'markup',
                      '#markup' => '<div class="timeControlWrapper controls"><button id="timeBack" class="timeButton"><i class="fas fa-angle-double-left"></i></button><span id="time">11.11.2022</span><button id="timeForward" class="timeButton"><i class="fas fa-angle-double-right"></i></button></div>',
                      '#allowed_tags' => ['div','span', 'i', 'button'],

                    ];
        */
        //Wms legend placeholder
        $build['search-map']['map-fullscreen-wrapper']['side-panel']['legend'] = [
          '#type' => 'markup',
          '#prefix' => '<div class="w3-container w3-margin-left legend-placeholder">',
          '#markup' => '<img id="map-wms-legend"/>',
          '#suffix' => '</div>',
          '#allowed_tags' => ['div','img'],

        ];
        /*
                $build['search-map']['map']['sidepanel']['timeslider'] = [
                  '#type' => 'markup',
                  '#markup' => '<div id="animatedWmsControls" class="ol-control"></div>',
                  '#allowed_tags' => ['div','img'],

                ];
        */
        //Placeholder for the ol-ext layerswitcher inside side-panel
        $build['search-map']['map-fullscreen-wrapper']['side-panel']['layerswitcher'] = [
            '#markup' => '<div class="external layerSwitcher"><b>Layer switcher</b></div>',
            '#allowed_tags' => ['div', 'b'],
        ];




        //Bottom -panel
        $build['search-map']['map-fullscreen-wrapper']['bottom-panel'] = [
      '#type' => 'markup',
      '#markup' => '<div id="bottomMapPanel" class="bottom-map-panel">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div'],
  ];
        //Progress bar wrapper
        $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['progress-bar'] = [
     '#type' => 'markup',
     '#prefix' => '<div class="progress-container">',
     '#markup' => '<div id="progress"></div>',
     '#suffix' => '</div>',
     '#allowed_tags' => ['div','span', 'i', 'button'],

   ];
        //Date controls wrapper
        $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['animated-controls'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="animatedWmsControls">',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div','span', 'i', 'button'],

    ];
        //Timeslider wrapper
        $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['animated-controls']['time-slider'] = [
       '#type' => 'markup',
       '#markup' => '<div id="map-timeslider-id"><div class="ui-slider-handle"></div></div>',
     ];

        //Timeslider wrapper
        $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['animated-controls']['time-controls'] = [
        '#type' => 'markup',
        '#markup' => '<div class="timeControlWrapper controls"><span>Time dimensions: </span><button id="timeBack" class="timeButton"><i class="fas fa-angle-double-left"></i></button><span id="time">11.11.2022</span><button id="timeForward" class="timeButton"><i class="fas fa-angle-double-right"></i></button></div>',
        '#allowed_tags' => ['div','span', 'i', 'button'],

      ];

        //Date controls wrapper
        $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['elevation-controls'] = [
          '#type' => 'markup',
          '#prefix' => '<div id="elevationWmsControls">',
          '#suffix' => '</div>',
          '#allowed_tags' => ['div','span', 'i', 'button'],

        ];
        //Timeslider wrapper
        $build['search-map']['map-fullscreen-wrapper']['bottom-panel']['elevation-controls']['elevation-buttons'] = [
            '#type' => 'markup',
            '#markup' => '<div class="elevationControlWrapper controls"><span>Elevation dimensions: </span> <button id="elevationUp" class="elevationButton"><i class="fas fa-angle-double-up"></i></button><span id="elevation" data-current=0>0</span><button id="elevationDown" class="elevationButton"><i class="fas fa-angle-double-down"></i></button></div>',
            '#allowed_tags' => ['div','span', 'i', 'button'],

          ];

        //Define popup markup
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



        //Placeholder for ts-plot
        $build['map-ts-plot'] = [
    '#prefix' => '<div id="bokeh-map-ts-plot" class="w3-card-2 w3-container">',
    '#suffix' => '</div>',
    '#allowed_tags' => ['div'],
  ];

        $build['map-ts-plot']['header'] = [
    '#type' => 'markup',
    '#markup' => '<div class="map-ts-header"><span class="w3-center"><h3>Visualize timeseries</h3></span></div>',
    '#allowed_tags' => ['div','h','h3', 'span'],
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

        /* $build['suffix'] = [
          '#suffix' => '</div>'
        ];
        */

        //Set the cache for this form
        $build['#cache'] = [
          //'max-age' => 0,
         //'tags' =>$this->getCacheTags(),
          'contexts' => [
          //  'route',

              'url.path',
              'url.query_args',
            ],
          ];

        // Add CSS and JS libraries and drupalSettings JS variables
        $build['#attached'] = [
      'library' => [
      'metsis_lib/adc-button',
      'metsis_ts_bokeh/style',
      'metsis_ts_bokeh/bokeh_js',
      'metsis_ts_bokeh/bokeh_widgets',
      'metsis_ts_bokeh/bokeh_tables',
      'metsis_ts_bokeh/bokeh_api',
      'blazy/load',
      'metsis_search/search_map_block',
      ],
    'drupalSettings' => [
    'metsis_search_map_block' => [
      'mapLat' => $map_lat, //to be replaced with configuration variables
      'mapLon' => $map_lon, //to be replaced with configuration variables
      'mapZoom' => $map_zoom, //to be replaced with configuration variables
      'init_proj' => $map_init_proj, //to be replaced with configuration variables
      'additional_layers' => $map_additional_layers, //to be replaced with configuration variables
      'base_layer_wms_north' => $map_base_layer_wms_north,
      'base_layer_wms_south' => $map_base_layer_wms_south,
      'projections' => $map_projections,
      'layers_list' => $map_layers_list,
      'tllat' => $tllat,
      'tllon' => $tllon,
      'brlon' => $brlon,
      'brlat' => $brlat,
      'proj' => $proj,
      'bboxFilter' => $bboxFilter,
      'mapFilter' => $map_filter,
      'pins' => $map_pins,
      'path' => $module_path,
      'extracted_info' => $extracted_info,
      'pywps_service' => $pywps_service,
      'current_search' => $searchUri,
      'wms_layers_skip' => $map_wms_layers_skip,
    ],
    ],
    ];

        //Set the id of the form
        /* $build['#attributes'] = [
          'id' => 'map-search',
        ];
        */
        //$session->set("place_filter", null);
        return $build;
    }
    public function getCacheMaxAge()
    {
        return 1;
    }
}

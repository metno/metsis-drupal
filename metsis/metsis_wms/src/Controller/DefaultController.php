<?php /**
 * @file
 * Contains \Drupal\metsis_wms\Controller\DefaultController.
 */

namespace Drupal\metsis_wms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Select\Result\Document;
use Solarium\Core\Query\DocumentInterface;
use Solarium\Core\Query\Result\ResultInterface;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\RedirectResponse;


use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;


/**
 * Default controller for the metsis_wms module.
 */
class DefaultController extends ControllerBase {

  public function get_custom_content() {
    $datasetURL = filter_input(INPUT_GET, "datasetURL");
    //var_dump($datasetURL);
    $content = [
    '#markup' => '<div class="map container"><div id="map"></div><div id="lyr-switcher"></div>' . '<div id="proj-container"></div><div id="timeslider-container"></div></div>' . '<div id="wmsURL" class="element-hidden">' . $datasetURL . '</div>',
  ];
    return $content;
  }

  public function getWmsMap()
  {
      $query_from_request = \Drupal::request()->query->all();
      $query = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
      $request = \Drupal::request();
      $referer = $request->headers->get('referer');

      $module_handler = \Drupal::service('module_handler');
      $module_path = $module_handler->getModule('metsis_search')->getPath();

      /** Variables from configuration
      *
      */
      //Get saved configuration
      $config = \Drupal::config('metsis_search.settings');
      $map_location = $config->get('map_selected_location');
      $map_lat =  $config->get('map_locations')[$map_location]['lat'];
      $map_lon = $config->get('map_locations')[$map_location]['lon'];
      $map_zoom = $config->get('map_zoom');
      $map_additional_layers = $config->get('map_additional_layers_b');
      $map_projections = $config->get('map_projections');
      $map_init_proj =  $config->get('map_init_proj');
      $map_layers_list =  $config->get('map_layers');
      $pywps_service = $config->get('pywps_service');
      $map_wms_layers_skip = explode(',', $config->get('map_wms_layers_skip'));

      $markup = 'No Data Found!';
      $webMapServers = [];
      \Drupal::logger('metsis_wms')->debug("Got query parameters: " . count($query));
      if (count($query) > 0) {
          $datasets = explode(",", $query['dataset']);
          $fields = [
            "id",
            "data_access_url_ogc_wms",
            "data_access_wms_layers",
            "metadata_identifier",
            'geographic_extent_rectangle_north',
            'geographic_extent_rectangle_south',
            'geographic_extent_rectangle_east',
            'geographic_extent_rectangle_west'
          ];
          /** @var Index $index  TODO: Change to metsis when prepeare for release */
          $index = Index::load('metsis');

          /** @var SearchApiSolrBackend $backend */
          $backend = $index->getServerInstance()->getBackend();

          $connector = $backend->getSolrConnector();

          $solarium_query = $connector->getSelectQuery();

          //foreach ($metadata_identifier as $id) {
          //    \Drupal::logger('metsis_wms')->debug("setQuery: metadata_identifier: " .$id);
          $ids = implode(' ', $datasets);
          $solarium_query->setQuery('metadata_identifier:('.$ids.')');
          //}
          //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
          //$solarium_query->setRows(2);
          $solarium_query->setFields($fields);

          $result = $connector->execute($solarium_query);

          // The total number of documents found by Solr.
          $found = $result->getNumFound();
          \Drupal::logger('metsis_wms')->debug("found :" .$found);
          // The total number of documents returned from the query.
          //$count = $result->count();

          // Check the Solr response status (not the HTTP status).
          // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
          //$status = $result->getStatus();

          $wms_data = [];
          $layers = [];
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
          }
         else {
            \Drupal::messenger()->addError(t("Selected datasets does not contain any WMS resource.<br> Visualization not possible"));
            return new RedirectResponse($referer);
        }
      }


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
      $build['search-map']['top-panel']['buttons-container']['go-back'] = [
        '#type' => 'markup',
        '#markup' => '<span id="goBackID"><a id="goBackMapButton" class="w3-center adc-button adc-sbutton" href="' . $referer .'">Go back to search</a></span>',
        '#allowed_tags' => ['div','label','button','br','a', 'span'],
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

      /*  $build['search-map']['top-panel']['opacity'] = [
      '#type' => 'markup',
      '#markup' => '<span class="w3-right">Opacity WMS Layers<div id="map-slider-id" class="w3-right"><div class="ui-slider-handle"></div></div></span>',
      '#allowed_tags' => ['div', 'span'],
      ];
      */

      //Placeholder for additional layers select list


      /**
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
         '#allowed_tags' => ['div','label'],
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


      //Wms legend placeholder
      $build['search-map']['map-fullscreen-wrapper']['side-panel']['legend'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="w3-container w3-margin-left legend-placeholder">',
        '#markup' => '<img id="map-wms-legend"/>',
        '#suffix' => '</div>',
        '#allowed_tags' => ['div','img'],

      ];

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
/*    $build['map-ts-plot'] = [
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
*/
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
    'metsis_lib/adc_buttons',
    'metsis_wms/wms_ol6',
    ],
    'drupalSettings' => [
      'metsis_wms_map' => [
        'mapLat' => $map_lat, //to be replaced with configuration variables
        'mapLon' => $map_lon, //to be replaced with configuration variables
        'mapZoom' => $map_zoom, //to be replaced with configuration variables
        'init_proj' => $map_init_proj, //to be replaced with configuration variables
        'additional_layers' => $map_additional_layers, //to be replaced with configuration variables
        'projections' => $map_projections,
        'layers_list' => $map_layers_list,
        'path' => $module_path,
        'pywps_service' => $pywps_service,
        'wms_layers_skip' => $map_wms_layers_skip,
        'wms_data' => $wms_data,
        ],
      ],
    ];

      //Set the id of the form
      /* $build['#attributes'] = [
        'id' => 'map-search',
      ];
      */

      return $build;

      }




}
}

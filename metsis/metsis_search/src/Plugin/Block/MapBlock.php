<?php
/**
 * @file
 * Contains \Drupal\metsis_search\Plugin\Block\MapBlock
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
 *   id = "metsis_search_map_block",
 *   admin_label = @Translation("Map Block for METSIS Search"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class MapBlock extends BlockBase implements BlockPluginInterface
{
    /**
     * {@inheritdoc}
     * Add js to block and return renderarray
     */
    public function build()
    {
        //\Drupal::logger('metsis_search')->debug("Building MapSearchForm");

        // Get the module path
        $module_handler = \Drupal::service('module_handler');
        $module_path = $module_handler->getModule('metsis_search')->getPath();
        // Get the bounding box drawn on the map
        //\Drupal::logger('metsis_search')->debug("Building MapSearchForm");
        //$tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
        //$bboxFilter = $tempstore->get('bboxFilter');
        $session = \Drupal::request()->getSession();
        $bboxFilter = $session->get('bboxFilter');
        $tllat = "";
        $tllon = "";
        $brlat = "";
        $brlon = "";
        $proj = $session->get('proj');
        //\Drupal::logger('metsis_search:mapBlock')->debug('current mapblock session projection: ' . $proj);

        if ($bboxFilter != null) {
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
        }

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


        //Get the extracted info from tempstore
        //$tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
        //$extracted_info = $session->get('extracted_info');

        if ($proj != null) {
            $map_init_proj = $proj;
        }

        $build['search-map'] = [
      '#prefix' => '<p><div id="map-search" class="w3-border map-search">',
      '#suffix' => '</div>'
    ];
        // search-map wrapper
        $build['search-map']['panel'] = [
      '#prefix' => '<div id="panel" class="panel">',
      '#markup' => $map_search_text,
      '#suffix' => '</div><br>'
    ];
        $build['search-map']['panel']['layers'] = [
      '#type' => 'markup',
      '#markup' => '<div class="layers-wrapper"></div>',
      '#allowed_tags' => ['div','label'],
    ];
        //Message to be displayed under the map
        $build['search-map']['projection'] = [
      '#type' => 'markup',
      '#markup' => '<div class="proj-wrapper"><label class="proj-label"><strong>Select Projection</strong></label><br></div>',
      '#allowed_tags' => ['div','label'],
    ];

        $build['map-div'] = [
      '#type' => 'markup',
      '#markup' => '<div id="metmap" class="metmap"></div>',
      '#allowed_tags' => ['div','label'],
    ];
        $build['suffix'] = [
      '#markup' => '<br><br>'
    ];

        $build['#cache'] = [
    //'max-age' => 0,
    //'tags' =>$this->getCacheTags(),
      'contexts' => [
        //  'route',

        #'url.path',
        'url.query_args',
      ],
    ];

        $build['#attached'] = [
      'library' => [
        'metsis_search/search_map'
      ],
      'drupalSettings' => [
        'metsis_search' => [
          'mapLat' => $map_lat, //to be replaced with configuration variables
          'mapLon' => $map_lon, //to be replaced with configuration variables
          'mapZoom' => $map_zoom, //to be replaced with configuration variables
          'init_proj' => $map_init_proj, //to be replaced with configuration variables
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
        ],
      ],


];
        //Return render array
        return $build;
    }
}

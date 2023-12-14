<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for handeling map search related stuff.
 */
class MapSearchController extends ControllerBase {

  /**
   * Callback from openlayers when boundingbox filter are drawed on map.
   *
   * Add current drawed boundingbox to solr search query.
   */
  public function setBoundingBox(Request $request) {
    $query_from_request = $request->query->all();
    $params = UrlHelper::filterQueryParameters($query_from_request);

    $tllat = $params['tllat'];
    $tllon = $params['tllon'];
    $brlat = $params['brlat'];
    $brlon = $params['brlon'];
    $proj = $params['proj'];
    $bboxFilter = 'ENVELOPE(' . $tllon . ',' . $brlon . ',' . $tllat . ',' . $brlat . ')';

    // Get current session variables.
    $session = $request->getSession();
    $session->set('bboxFilter', $bboxFilter);
    $session->set('tllat', $tllat);
    $session->set('tllon', $tllon);
    $session->set('brlat', $brlat);
    $session->set('brlon', $brlon);
    $session->set('proj', $proj);

    // Get saved configuration.
    $config = $this->config('metsis_search.settings');
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

    // Check if user have selected spatial filter. If not use default config.
    if (isset($params['cond']) && $params['cond'] !== "") {
      $map_filter = $params['cond'];
      // $session->set('cond', $map_filter);
    }
    else {
      $map_filter = $config->get('map_bbox_filter');
    }
    $session->set('cond', $map_filter);
    $data = [
      'metsis_search_map_block' => [
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
        'tllat' => $tllat,
        'tllon' => $tllon,
        'brlon' => $brlon,
        'brlat' => $brlat,
        'proj' => $proj,
        'base_layer_wms_north' => $map_base_layer_wms_north,
        'base_layer_wms_south' => $map_base_layer_wms_south,
        'projections' => $map_projections,
        'layers_list' => $map_layers_list,
        'bboxFilter' => $bboxFilter,
        'mapFilter' => $map_filter,
      ],
    ];
    $response = new AjaxResponse();
    $response->addCommand(new SettingsCommand($data, TRUE));

    return $response;
  }

  /**
   * Select projection callback .
   */
  public function setProjection(Request $request) {
    $query_from_request = $request->query->all();
    $params = UrlHelper::filterQueryParameters($query_from_request);

    $proj = $params['proj'];
    // Get current session variables.
    $session = $request->getSession();
    $session->set('proj', $proj);

    $data = [
      'metsis_search_map_block' => [
        'proj' => $proj,
      ],
    ];
    $response = new AjaxResponse();
    $response->addCommand(new SettingsCommand($data, TRUE));

    return $response;
  }

  /**
   * Callback when using geocoder to search for place.
   */
  public function setPlace(Request $request) {
    $query_from_request = $request->query->all();
    $params = UrlHelper::filterQueryParameters($query_from_request);

    $tllat = $params['tllat'];
    $tllon = $params['tllon'];
    $brlat = $params['brlat'];
    $brlon = $params['brlon'];
    $proj = $params['proj'];
    $bboxFilter = 'ENVELOPE(' . $tllon . ',' . $brlon . ',' . $tllat . ',' . $brlat . ')';

    // Get current session variables.
    $session = $request->getSession();
    $session->set('bboxFilter', $bboxFilter);
    $session->set('tllat', $tllat);
    $session->set('tllon', $tllon);
    $session->set('brlat', $brlat);
    $session->set('brlon', $brlon);
    $session->set('proj', $proj);
    $session->set('place_filter', "Contains");

    $response = new AjaxResponse();
    // \Drupal::logger('metsis_search_map_search_controller')->debug(\Drupal::request()->getRequestUri());
    return $response;
    // Return $this->redirect(\Drupal::request()->getRequestUri());
  }

  /**
   * Reset the search.
   */
  public function reset(Request $request) {
    // Get current session variables.
    $session = $request->getSession();
    // $tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
    $session->remove('bboxFilter');
    $session->remove('tllat');
    $session->remove('tllon');
    $session->remove('brlat');
    $session->remove('brlon');
    $session->remove('extracted_info');
    $session->remove('proj');
    $session->remove('gcmd');
    $session->remove('keywords_level');
    $session->remove('back_to_search');
    $session->remove('place_filter');
    $session->remove('place_lat');
    $session->remove('place_lon');
    $session->set('keywords_level', 1);

    // $session->remove('proj', $proj);
    // $session->remove('place_filter');
    // $response = new AjaxResponse();
    return new RedirectResponse(Url::fromRoute('view.metsis_search.results')->toString());
  }

  /**
   * Callback for reset action.
   */
  public function resetCallback() {
    // \Drupal::logger('metsis_search')->debug("MapSearchController::resetCallback");
  }

}

<?php /**
 * @file
 * Contains \Drupal\metsis_qsearch\Controller\WmsController.
 */

namespace Drupal\metsis_qsearch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
/**
 * Default controller for the metsis_qsearch module.
 */
class WmsController extends ControllerBase {

  public function getWmsMap() {
    $query_from_request = \Drupal::request()->query->all();
    $query = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');
    //var_dump($query);
    //var_dump($referer);
    $page = 'No Data Found!';
    if (count($query) > 0) {
      $datasets = explode(",", $query['dataset']);
      $externalURL = $datasets;
      if (isset($query['solr_core'])) {
        $solr_core = $query['solr_core'];
        $page = get_metsis_map_wms_markup($solr_core, $externalURL);
      }
      else {
        $page = get_metsis_map_wms_markup($externalURL);
      }
    }
    //Return $page as renderarray
    return [
      '#type' => '#markup',
      '#markup' => $page,
      '#attached' => array(
        'library' => array(
          'metsis_wms/replace.css',
          'metsis_wms/replace.jquery_min',
          'metsis_wms/replace.jquery_core',
          'metsis_wms/replace.jquery_bbq',
          'metsis_wms/replace.misc_overlay',
          'metsis_wms/replace.jquery_cookie',
          'metsis_lib/utils',
          'metsis_wms/bundle',
          'metsis_lib/adc_buttons'
        ),
      ),
     '#allowed_tags' => ['div','script', 'a'],
    ];
  }

}

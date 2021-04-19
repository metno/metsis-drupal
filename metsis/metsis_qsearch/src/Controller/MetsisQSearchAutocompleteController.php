<?php /**
 * @file
 * Contains \Drupal\metsis_qsearch\Controller\MetsisQSearchAutocompleteController.
 */

namespace Drupal\metsis_qsearch\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Xss;

/**
 * Default controller for the metsis_qsearch module.
 */
class MetsisQSearchAutocompleteController extends ControllerBase {

  public function keywords(Request $request) {
      $input = $request->query->get('q');

      // Get the typed string from the URL, if it exists.
      if (!$input) {
        return new JsonResponse($results);
      }
      $input = Xss::filter($input);

      $matches =[];
      $mmd_keywords = \Drupal::state()->get(METADATA_PREFIX . 'keywords');
      foreach ($mmd_keywords as $kw) {
        if (stristr($kw, $input)) {
          $matches[] = [
            'value' => $kw,
            'label' => $kw,
          ];
        }
      }
  
  return new JsonResponse($matches);
  }
}

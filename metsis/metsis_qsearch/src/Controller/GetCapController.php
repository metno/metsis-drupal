<?php
/**
 * @file
 * GetCap controller for the metsis_qsearch module.
 */
namespace Drupal\metsis_qsearch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Response;

class GetCapController extends ControllerBase {

  public function getCapDoc() {
    /**
     * Get the query parameters from the calling pre_page
     */
    $query_from_request = \Drupal::request()->query->all();
    $query = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
    if (count($query) > 0) {
      $url = $query['dataset'] . '&REQUEST=' . $query['REQUEST'];

      // Make the xml request on thredds wms service.
      $options = [
        'connect_timeout' => 30,
        'debug' => false,
        'headers' => array(
          'Content-Type' => 'application/xml',
        )
    //    'body' => $xml,
    //    'verify'=>true,
      ];

      try {
        $client = \Drupal::httpClient();
        $request = $client->request('GET',$url,$options);

      }
      catch (RequestException $e){
        // Log the error.
        watchdog_exception('custom_modulename', $e);
      }
      // Get the response
      $responseStatus = $request->getStatusCode();
      //var_dump($responseStatus);
      $responseXml = $request;
      //Create a Drupal xml response and return the response to the page
      $response = new Response(
        $responseXml->getBody(),
        Response::HTTP_OK,
        array('Content-Type' => 'application/xml|')
      );
      return  $response;
    }

  }
}

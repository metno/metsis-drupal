<?php

namespace Drupal\metsis_wms\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class for getting the wms capabilites document.
 */
class GetCapController extends ControllerBase {

  /**
   * Get the capabilities document.
   */
  public function getCapDoc() {
    /*
     * Get the query parameters from the calling pre_page.
     */
    $query_from_request = \Drupal::request()->query->all();
    $query = UrlHelper::filterQueryParameters($query_from_request);
    if (count($query) > 0) {
      $url = $query['dataset'] . '&REQUEST=' . $query['REQUEST'];

      // Make the xml request on thredds wms service.
      $options = [
        'connect_timeout' => 30,
        'debug' => FALSE,
        'headers' => [
          'Content-Type' => 'application/xml',
        ],
          // 'body' => $xml,
          // 'verify'=>true,
      ];

      try {
        $client = \Drupal::httpClient();
        $request = $client->request('GET', $url, $options);
      }
      catch (RequestException $e) {
        // Log the error.
        watchdog_exception('custom_modulename', $e);
      }
      // Get the response.
      $responseStatus = $request->getStatusCode();
      // var_dump($responseStatus);
      $responseXml = $request;
      // Create a Drupal xml response and return the response to the page.
      $response = new Response(
            $responseXml->getBody(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml|']
        );
      return $response;
    }
  }

  /**
   * Get the capdoc from url.
   */
  public function getCapDocFromUrl() {
    /*
     * Get the query parameters from the calling pre_page
     */
    $query_from_request = \Drupal::request()->query->all();
    $query = UrlHelper::filterQueryParameters($query_from_request);
    $url = $query['url'];

    $host = \Drupal::request()->getSchemeAndHttpHost();
    // $getCapString = '?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities';
    $getCapString = '?VERSION=1.3.0&REQUEST=GetCapabilities&SERVICE=WMS';
    $getCapUrl = $url . $getCapString;
    \Drupal::logger('metsis_wms::getCapDocFromUrl')->debug($getCapUrl);
    // $getCapUrl = 'https://sampleserver1.arcgisonline.com/ArcGIS/services/Specialty/ESRI_StatesCitiesRivers_USA/MapServer/WMSServer?version=1.3.0&request=GetCapabilities&service=WMS';
    // Make the xml request on thredds wms service.
    $options = [
      'connect_timeout' => 30,
      'debug' => FALSE,
      'headers' => [
        'Accept' => 'application/xml',
        // 'Content-Type' => 'application/xml',
        'Content-Type' => 'application/xml',
        'Origin' => $host,
        // 'Accept-Encoding' => 'gzip, deflate',
      ],
      // 'body' => $xml,
      // 'verify'=>true,
    ];

    try {
      $client = \Drupal::httpClient();
      $response = $client->request('GET', $getCapUrl, $options);
    }
    catch (RequestException $e) {
      // Log the error.
      \Drupal::logger('metsis_wms')->notice('WMS with url: ' . $getCapUrl . ' was not found.');
      return new Response(
            '404: Not found',
            Response::HTTP_NOT_FOUND,
            ['Content-Type' => 'application/xml']
            );
    }
    if (!is_null($response)) {
      // Get the response.
      $responseStatus = $response->getStatusCode();

      $responseXml = $response;
      // Create a Drupal xml response and return the response to the page.
      $responseCustom = new Response(
            $responseXml->getBody(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml']
        );
      // $body = (string) $responseCustom;
      $body = (string) $response->getBody();
      $xml = simplexml_load_string($body);
      $json = Json::encode($xml);
      // \Drupal::logger('metsis_wms::getCapDocFromUrl')->debug('json: @json', [ '@json' => $json]);
      // $array = json_decode($json,TRUE);
      // \Drupal::logger('metsis_wms::getCapDocFromUrl')->debug('xml @xml', [ '@xml' => $body]);
      // \Drupal::logger('metsis_wms::getCapDocFromUrl')->debug('Got response status: ' . $responseStatus);
      // \Drupal::logger('metsis_wms::getCapDocFromUrl')->debug('Got response body: @body', ['@body' => $client->$request->getContents()]);
      // \Drupal::logger('metsis_wms::getCapDocFromUrl')->debug('Custom response body: ' . $responseCustom);
      $jsonResponse = new JsonResponse();
      // $jsonResponse->setData(Json::decode($json));
      $jsonResponse->setJson($json);
      // Return $jsonResponse;
      // return  new AjaxResponse();
      return $responseCustom;
    }
  }

}

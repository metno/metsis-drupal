<?php

namespace Drupal\metsis_wms\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class for getting the wms capabilites document.
 */
class GetCapController extends ControllerBase {
  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructor for MymoduleServiceExample.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   A Guzzle client object.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('http_client')
    );
  }

  /**
   * Get the capabilities document.
   */
  public function getCapDoc(Request $request) {
    /*
     * Get the query parameters from the calling pre_page.
     */
    $query_from_request = $request->query->all();
    $query = UrlHelper::filterQueryParameters($query_from_request);
    if (count($query) > 0) {
      $url = $query['dataset'] . '&REQUEST=' . $query['REQUEST'];

      // Make the xml request on thredds wms service.
      $options = [
        'connect_timeout' => 30,
        'debug' => FALSE,
        'headers' => [
          'Content-Type' => 'application/xml',
          'Access-Control-Allow-Origin' => '*',
        ],
          // 'body' => $xml,
          // 'verify'=>true,
      ];

      try {
        $request = $this->httpClient->request('GET', $url, $options);
      }
      catch (RequestException $e) {
        // Log the error.
        $this->getLogger('metsis_wms:getCapDoc')->error(str($e));
      }
      // Get the response.
      // $responseStatus = $request->getStatusCode();
      // var_dump($responseStatus);
      $responseXml = $request;
      // Create a Drupal xml response and return the response to the page.
      $response = new Response(
            $responseXml->getBody(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml']
        );
      return $response;
    }
  }

  /**
   * Get the capdoc from url.
   */
  public function getCapDocFromUrl(Request $request) {
    /*
     * Get the query parameters from the calling pre_page
     */
    $query_from_request = $request->query->all();
    $query = UrlHelper::filterQueryParameters($query_from_request);
    $url = $query['url'];

    $host = $request->getSchemeAndHttpHost();
    // $getCapString = '?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities';
    $getCapString = '?VERSION=1.3.0&REQUEST=GetCapabilities&SERVICE=WMS';
    if (str_contains($url, 'mapserver.wps.met.no')) {
      $getCapUrl = $url . '&service=WMS&version=1.3.0&request=GetCapabilities';
    }
    else {
      $getCapUrl = $url . $getCapString;
    }
    $this->getLogger('metsis_wms::getCapDocFromUrl')->notice($getCapUrl);
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
      $response = $this->httpClient->request('GET', $getCapUrl, $options);
    }
    catch (RequestException $e) {
      // Log the error.
      $this->getLogger('metsis_wms')->notice('WMS with url: ' . $getCapUrl . ' was not found.');
      return new Response(
            '404: Not found',
            Response::HTTP_NOT_FOUND,
            ['Content-Type' => 'application/xml']
            );
    }
    if (!is_null($response)) {
      // Get the response.
      // $responseStatus = $response->getStatusCode();
      $responseXml = $response;
      // Create a Drupal xml response and return the response to the page.
      $responseCustom = new Response(
            $responseXml->getBody(),
            Response::HTTP_OK,
            [
              'Content-Type' => 'application/xml',
              'Access-Control-Allow-Origin' => '"*"',
              'Access-Control-Allow-Headers' => '"*"',
              'Access-Control-Allow-Methods' => 'GET, HEAD, POST, OPTIONS',
            ]
        );
      // $body = (string) $responseCustom;
      $body = (string) $response->getBody();
      $xml = simplexml_load_string($body);
      $json = Json::encode($xml);
      $jsonResponse = new JsonResponse();
      // $jsonResponse->setData(Json::decode($json));
      $jsonResponse->setJson($json);
      // Return $jsonResponse;
      // return  new AjaxResponse();
      return $responseCustom;
    }
  }

}

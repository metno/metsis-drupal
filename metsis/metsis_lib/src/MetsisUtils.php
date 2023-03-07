<?php

namespace Drupal\metsis_lib;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\search_api\Entity\Index;

/**
 * Static class for functions used by multiple modules.
 */
class MetsisUtils {

  /**
   * Hold configs.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityInterface
   */
  protected $config;

  /**
   * Class constructor.
   */
  public function __construct(ConfigEntityInterface $config) {
    $this->$config = $config->get('metsis_lib.settings');
  }

  /**
   * Get current config.
   */
  public function getConfig() {
    return $this->$config;
  }

  /**
   * Get OD variables from OPeNDAP parser service.
   */
  public static function adcGetOdGlobalAttributes($metadata_identifier, $collection_core) {
    /*
     * Get the OPeNDAP parser service config
     */
    $config = \Drupal::config('metsis_lib.settings');
    $od_server_ip = $config->get('metsis_opendap_parser_ip');
    $od_server_port = $config->get('metsis_opendap_parser_port');
    $od_server_service = $config->get('metsis_opendap_parser_service');

    // Create uri from config:
    $uri = $od_server_ip . ':' . $od_server_port . $od_server_service;
    // var_dump($uri);
    // Get the referer:
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');

    $odquery = '{
                findAllAttributes(
                  datasetId: "' . $metadata_identifier . '", collection: "' . $collection_core . '"
                    ) {
                        name value
                    }
               }';

    $con = new HttpConnection($od_server_ip, $od_server_port);
    $res = $con->get($od_server_service, ["query" => $odquery]);
    $jres = Json::decode($res['body'], TRUE);
    return $jres;
  }

  /**
   * Get DAP variables.
   */
  public static function adcGetOdVariables($metadata_identifier, $collection_core) {
    /*
     * Get the OPeNDAP parser service config
     */
    $config = \Drupal::config('metsis_lib.settings');
    $od_server_ip = $config->get('metsis_opendap_parser_ip');
    $od_server_port = $config->get('metsis_opendap_parser_port');
    $od_server_service = $config->get('metsis_opendap_parser_service');

    // Create uri from config:
    $uri = $od_server_ip . ':' . $od_server_port . $od_server_ip;

    // Get the referer:
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');

    $odquery = '{
                      findAllVariables(
                        datasetId: "' . $metadata_identifier . '", collection: "' . $collection_core . '"
                          ) {
                              name
                                   attributes {
                                     name value
                                      }
                          }
                     }';

    $con = new HttpConnection($od_server_ip, $od_server_port);
    $res = $con->get($od_server_service, ["query" => $odquery]);
    $jres = Json::decode($res['body'], TRUE);
    return $jres;
  }

  /**
   * Get fields.
   */
  public static function msbGetFields($metadata_identifier, $fields) {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();

    foreach ($metadata_identifier as $id) {
      \Drupal::logger('metsis_lib')->debug("setQuery: metadata_identifier: " . $id);
      $solarium_query->setQuery('metadata_identifier:' . $id);
    }
    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
    // $solarium_query->setRows(2);.
    $solarium_query->setFields($fields);

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    \Drupal::logger('metsis_lib')->debug("found :" . $found);
    // The total number of documents returned from the query.
    // $count = $result->count();
    // Check the Solr response status (not the HTTP status).
    // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
    // $status = $result->getStatus();
    // An array of documents. Can also iterate directly on $result.
    return $result;
  }

  /**
   * Get the date as metsis dateformat ISO.
   */
  public static function getMetsisDate($date_string, $format) {
    $d = new \DateTime($date_string);
    return $d->format($format);
  }

  /**
   * Get the title.
   */
  public static function msbGetTitle($metadata_identifier) {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();

    \Drupal::logger('metsis_lib')->debug("setQuery: metadata_identifier: " . $metadata_identifier);
    $solarium_query->setQuery('metadata_identifier:' . $metadata_identifier);

    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
    // $solarium_query->setRows(2);.
    $solarium_query->setFields('title');

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    \Drupal::logger('metsis_lib')->debug("found :" . $found);
    // The total number of documents returned from the query.
    // $count = $result->count();
    // Check the Solr response status (not the HTTP status).
    // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
    // $status = $result->getStatus();
    $fields = NULL;
    foreach ($result as $doc) {
      $fields = $doc->getFields();

    }
    if (isset($fields['title'])) {
      // An array of documents. Can also iterate directly on $result.
      return $fields['title'][0];
    }
    else {
      return 'No Title';
    }
  }

  /**
   * Get resources.
   */
  public static function msbGetResources($metadata_identifier) {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();

    \Drupal::logger('metsis_lib')->debug("setQuery: metadata_identifier: " . $metadata_identifier);
    $solarium_query->setQuery('metadata_identifier:' . $metadata_identifier);

    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
    // $solarium_query->setRows(2);.
    $solarium_query->setFields([
      'data_access_url_http',
      'data_access_url_odata',
      'data_access_url_opendap',
      'data_access_url_ogc_wms',
    ]);

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    \Drupal::logger('metsis_lib')->debug("found :" . $found);
    // The total number of documents returned from the query.
    // $count = $result->count();
    // Check the Solr response status (not the HTTP status).
    // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
    // $status = $result->getStatus();
    $fields = NULL;
    $dar = [];
    foreach ($result as $doc) {
      $fields = $doc->getFields();

    }
    if (isset($fields['data_access_url_http'])) {
      // An array of documents. Can also iterate directly on $result.
      $dar['http'] = $fields['data_access_url_http'];
    }
    if (isset($fields['data_access_url_odata'])) {
      // An array of documents. Can also iterate directly on $result.
      $dar['odata'] = $fields['data_access_url_odata'];
    }
    if (isset($fields['data_access_url_opendap'])) {
      // An array of documents. Can also iterate directly on $result.
      $dar['opendap'] = $fields['data_access_url_opendap'];
    }
    if (isset($fields['data_access_url_ogc_wms'])) {
      // An array of documents. Can also iterate directly on $result.
      $dar['ogc_wms'] = $fields['data_access_url_ogc_wms'];
    }
    return $dar;
  }

  /**
   * Get feature type.
   */
  public static function msbGetFeatureType($metadata_identifier) {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();

    \Drupal::logger('metsis_lib')->debug("setQuery: metadata_identifier: " . $metadata_identifier);
    $solarium_query->setQuery('metadata_identifier:' . $metadata_identifier);

    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
    // $solarium_query->setRows(2);.
    $solarium_query->setFields([
      'feature_type',
    ]);

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    \Drupal::logger('metsis_lib')->debug("found :" . $found);
    // The total number of documents returned from the query.
    // $count = $result->count();
    // Check the Solr response status (not the HTTP status).
    // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
    // $status = $result->getStatus();
    $fields = NULL;
    foreach ($result as $doc) {
      $fields = $doc->getFields();

    }
    if (isset($fields['feature_type'])) {
      return $fields['feature_type'];
    }
    else {
      return 'NA';
    }
  }

}

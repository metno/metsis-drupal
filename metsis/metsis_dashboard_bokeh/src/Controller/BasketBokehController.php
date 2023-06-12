<?php

namespace Drupal\metsis_dashboard_bokeh\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the metsis_basket module.
 *
 * {@inheritdoc}
 */
class BasketBokehController extends ControllerBase {

  /**
   * Add opendap resources to private tempstore basket.
   */
  public function add($metaid) {
    // \Drupal::logger('metsis_basket_controller')->debug("/metsis/basket/add");
    $opendap_uris = $this->getResources($metaid);

    $opendap_uri = urldecode($opendap_uri);

    $selector = '#myBasketCount';

    $tempstore = \Drupal::service('tempstore.private');
    // Get the store collection.
    $store = $tempstore->get('metsis_dashboard_bokeh');
    $datasources = $store->get('basket');
    if ($datasources != NULL) {
      foreach ($opendap_uris as $opendap_uri) {
        $basket_count = array_unshift($datasources, $opendap_uri);
      }
    }
    else {
      $datasources = [];
      foreach ($opendap_uris as $opendap_uri) {
        $basket_count = array_unshift($datasources, $opendap_uri);
      }
    }
    $store->set('basket', $datasources);
    // $basket_count = $this->get_user_item_count($store);
    $markup = '<span id="myBasketCount" class="w3-badge w3-green">' . $basket_count . '</span>';

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#addtobasket-' . $metaid, 'Add to Basket &#10004;'));
    // $response->addCommand(new ReplaceCommand($selector,$markup));
    $response->addCommand(new HtmlCommand($selector, $basket_count));
    $response->addCommand(new MessageCommand("Dataset added to basket:  " . $metaid));

    return $response;
  }

  /**
   * Get the opendap_uris from the given metadata id.
   */
  public function getResources($metaid) {

    /** @var Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();

    // \Drupal::logger('metsis_dashboard_bokeh')->debug("setQuery: metadata_identifier: " .$metaid);
    $solarium_query->setQuery('metadata_identifier:' . $metaid);

    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
    // $solarium_query->setRows(2);
    $solarium_query->setFields([
      'data_access_url_opendap',
    ]);

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    // \Drupal::logger('metsis_dashboard_bokeh')->debug("found :" .$found);
    // The total number of documents returned from the query.
    // $count = $result->count();
    // Check the Solr response status (not the HTTP status).
    // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
    // $status = $result->getStatus();
    $fields = NULL;

    $fields = $doc->getFields();

    $opendap_uris = [];
    if (isset($fields['data_access_url_opendap'])) {
      // An array of documents. Can also iterate directly on $result.
      $opendap_uris = $fields['data_access_url_opendap'];
    }
    return $opendap_uris;
  }

}

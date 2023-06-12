<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RemoveCommand;

use Drupal\Core\Controller\ControllerBase;
use Drupal\search_api\Entity\Index;

/**
 * A Class with functions used by the search interface.
 */
class MetsisSearchController extends ControllerBase {

  /**
   * Ajax callback to get the count of children datasets for a parent dataset.
   */
  public function getChildrenCount() {
    $query_from_request = \Drupal::request()->query->all();
    $params = UrlHelper::filterQueryParameters($query_from_request);
    $id = $params['metadata_identifier'];

    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    $solarium_query->setQuery('related_dataset_id:' . $id);
    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
    $solarium_query->setRows(1);
    $solarium_query->setFields('id');
    $solarium_query->createFilterQuery('children')->setQuery('isChild:true');

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();

    // The total number of documents returned from the query.
    // $count = $result->count();
    // Check the Solr response status (not the HTTP status).
    // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
    // $status = $result->getStatus();
    // An array of documents. Can also iterate directly on $result.
    // $documents = $result->getDocuments();
    // \Drupal::logger('metsis_search')->debug('Got ' . $found . ' children for dataset ' . $id);.
    $response = new AjaxResponse();
    if ($found > 0) {
      $selector = '.childlink[reference="' . $id . '"]';
      // $markup = '<a href="/metsis/elements?metadata_identifier="'. $id .'"/>Child data..['. $found .']</a>';
      $markup = 'Child data..[' . $found . ']';
      // \Drupal::logger('metsis_search')->debug("MetsisSearchController::getChildrenCount: markup: ". $markup );
      $response->addCommand(new HtmlCommand($selector, $markup));
    }
    if ($found == 0) {
      $response->addCommand(new RemoveCommand($selector));
    }
    /*
    else {
    $data = [
    'success' => true,
    //'count' => $found,
    ];
    $response->setData($data);
    }
     */
    return $response;
  }

}

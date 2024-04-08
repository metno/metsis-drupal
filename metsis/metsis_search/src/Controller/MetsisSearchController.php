<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RemoveCommand;

use Drupal\Core\Controller\ControllerBase;
use Drupal\search_api\Entity\Index;
use Symfony\Component\HttpFoundation\Request;

/**
 * A Class with functions used by the search interface.
 */
class MetsisSearchController extends ControllerBase {

  /**
   * Ajax callback to get the count of children datasets for a parent dataset.
   */
  public function getChildrenCount(Request $request) {
    $query_from_request = $request->query->all();
    $params = UrlHelper::filterQueryParameters($query_from_request);
    $id = $params['metadata_identifier'];
    $start_date = $params['start_date'] ?? NULL;
    $end_date = $params['end_date'] ?? NULL;

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
    $solarium_query->createFilterQuery('statusfilter')->setQuery('metadata_status:Active');
    $date_filter = '';
    if (NULL != $start_date) {
      $date_filter .= '+temporal_extent_start_date:[' . $start_date . 'T00:00:00Z TO *] ';
    }
    if (NULL != $end_date) {
      $date_filter .= '+temporal_extent_end_date:[* TO ' . $end_date . 'T00:00:00Z] ';
    }
    if ('' !== $date_filter) {
      $solarium_query->createFilterQuery('datefilter')->setQuery($date_filter);
    }
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
    $response = new AjaxResponse();
    $selector = '.childlink[reference="' . $id . '"]';
    if ($found > 0) {
      $markup = 'Child data..[' . $found . ']';
      $response->addCommand(new HtmlCommand($selector, $markup));
    }
    if ($found === 0) {
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

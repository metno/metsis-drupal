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
    // dpm($params, __FUNCTION__);.
    $id = $params['metadata_identifier'];
    $start_date = $params['start_date'] ?? NULL;
    $end_date = $params['end_date'] ?? NULL;
    $fulltext = $params['fulltext'] ?? NULL;
    $fulltext_op = $params['search_api_fulltext_op'] ?? NULL;
    // dpm($start_date);

    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    if (NULL != $fulltext) {
      $solarium_query->setQuery('related_dataset_id:' . $id .
        ' AND full_text:(' . $fulltext . ')');
    }
    else {
      $solarium_query->setQuery('related_dataset_id:' . $id);
    }
    if (NULL != $fulltext_op) {
      $fulltext_op = strtolower($fulltext_op);
      if ($fulltext_op === 'and') {
        $solarium_query->setQueryDefaultOperator($solarium_query::QUERY_OPERATOR_AND);
      }
      if ($fulltext_op === 'or') {
        $solarium_query->setQueryDefaultOperator($solarium_query::QUERY_OPERATOR_OR);
      }
    }

    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
    $solarium_query->setRows(1);
    $solarium_query->setFields('id');
    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $total = $result->getNumFound();

    $solarium_query->createFilterQuery('children')->setQuery('isChild:true');
    $solarium_query->createFilterQuery('statusfilter')->setQuery('metadata_status:Active');
    $date_filter = '';
    if (NULL != $start_date) {

      $date_filter .= '(temporal_extent_start_date:[' . $start_date . 'T00:00:00Z TO *])';
      // $date_filter .= ' AND temporal_extent_end_date:[* TO' . $start_date . '])';
      $date_filter .= ' OR (temporal_extent_start_date:[* TO ' . $start_date . 'T00:00:00Z]';
      $date_filter .= ' AND (*:* -temporal_extent_end_date:* ))';

    }

    if (NULL != $end_date) {
      $date_filter .= '(temporal_extent_end_date:[* TO ' . $end_date . 'T23:59:59Z])';
      $date_filter .= 'OR (temporal_extent_end_date:[' . $end_date . 'T00:00:00Z TO *] ';
      $date_filter .= ' AND temporal_extent_start_date:[ * TO ' . $end_date . 'T00:00:00Z]) ';
      $date_filter .= 'OR (*:* -temporal_extent_end_date:*)';

    }
    if (NULL != $end_date && NULL != $start_date) {
      $start = $start_date;
      $end = $end_date;
      $date_filter = '';
      $date_filter = ' ((temporal_extent_start_date:[' . $start . 'T00:00:00Z TO ' . $end . 'T23:59:59Z]';
      $date_filter .= ' AND temporal_extent_end_date:[' . $start . 'T00:00:00Z TO ' . $end . 'T23:59:59Z])';
      $date_filter .= ' OR (temporal_extent_start_date:[* TO ' . $start . 'T00:00:00Z]';
      $date_filter .= ' AND -temporal_extent_end_date:[* TO *]))';
      $date_filter .= ' OR ((temporal_extent_start_date:[* TO ' . $start . 'T00:00:00Z]';
      $date_filter .= ' AND temporal_extent_end_date:[' . $end . 'T00:00:00Z TO *])';
      $date_filter .= ' OR (temporal_extent_start_date:[* TO ' . $start . 'T00:00:00Z]';
      $date_filter .= ' AND -temporal_extent_end_date:[* TO *]))';
    }
    if ('' !== $date_filter) {
      // dpm($date_filter, __FUNCTION__);.
      $solarium_query->createFilterQuery('datefilter')->setQuery($date_filter);
    }
    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    // dpm($found, __FUNCTION__);
    // dpm($total, __FUNCTION__);.

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
      $markup = 'Child data..[' . $found . ' of ' . $total . ']';
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

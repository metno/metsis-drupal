<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;



class MetsisExportController extends ControllerBase {

    /**
    * Ajax callback to get the count of children datasets for a parent dataset
    */
    public function export($data) {
      /** @var Index $index  TODO: Change to metsis when prepeare for release */
      $index = Index::load('metsis');

      /** @var SearchApiSolrBackend $backend */
      $backend = $index->getServerInstance()->getBackend();

      $connector = $backend->getSolrConnector();

      $solarium_query = $connector->getSelectQuery();
      $solarium_query->setQuery('id:'.$data);
      //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
      $solarium_query->setRows(1);
      $fields[] = 'id';
      $fields[] = 'mmd_xml_file';
      $solarium_query->setFields($fields);

      $result = $connector->execute($solarium_query);

      // The total number of documents found by Solr.
      $found = $result->getNumFound();
      \Drupal::logger('export_doc')->debug("found: " . $found);
      $mmd = null;
      foreach($result as $doc) {
        $fields = $doc->getFields();
        //\Drupal::logger('export_doc')->debug($doc);
        $mmd = $fields['mmd_xml_file'];
      }
/*
      $build['examples_link'] = [
        '#title' => 'test',
        '#type' => 'link',
        '#url' => 'data:text/xml;base64,' .base64_decode($mmd),
];
       return $build; */
    /*   return [
         '#type' => 'markup',
         '#markup' => '<div id="exportxml" class="w3-container"></div>',
         '#allowed_tags' => ['div'],
         '#attached' => [
           'library' => [
             'metsis_search/export',
           ],

           'drupalSettings' => [
             'metsis_export' => [
               'xml' => $mmd,

           ],
         ],
       ],
     ];*/
     // This is the "magic" part of the code.  Once the data is built, we can
   // return it as a response.
   $response = new Response();

   // By setting these 2 header options, the browser will see the URL
   // used by this Controller to return a CSV file called "article-report.csv".
   $response->headers->set('Content-Type', 'text/xml');
   $response->headers->set('Content-Disposition', 'attachment; filename="mmd_export.xml"');

   // This line physically adds the CSV data we created
   $response->setContent(base64_decode($mmd));

   return $response;


   }
}

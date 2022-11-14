<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;

class MetsisThumbnailController extends ControllerBase
{
    //Querey the document for the given id and return the thumbnail.
    public function loadThumbnails($id)
    {

  /** @var Index $index  TODO: Change to metsis when prepeare for release */
        $index = Index::load('metsis');

        /** @var SearchApiSolrBackend $backend */
        $backend = $index->getServerInstance()->getBackend();

        $connector = $backend->getSolrConnector();

        $solarium_query = $connector->getSelectQuery();
        $solarium_query->setQuery('id:'.$id);
        //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
        $solarium_query->setRows(1);
        $solarium_query->setFields('thumbnail_data');


        $result = $connector->execute($solarium_query);

        // The total number of documents found by Solr.
        $found = $result->getNumFound();

        // The total number of documents returned from the query.
        //$count = $result->count();

        // Check the Solr response status (not the HTTP status).
        // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
        //$status = $result->getStatus();

        // An array of documents. Can also iterate directly on $result.
        //$documents = $result->getDocuments();

        //\Drupal::logger('metsis_search')->debug('Got ' . $found . ' children for dataset ' . $id);
        //$thumb = '/modules/metsis/metsis_search/images/missing_map_place_holder.png';
        $thumb = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        $response = new AjaxResponse();
        dpm($found);
        if ($found > 0) {
            foreach ($result as $doc) {
                foreach ($doc as $field => $value) {
                    if ($field === 'thumbnail_data') {
                        $thumb = $value;
                        dpm('got thumb: ' . $thumb);
                        $response->addCommand(new ReplaceCommand('#thumb-'.$id, '<img class="w3-image" src="' .$thumb.'" typeof="Image" style="width:70%;max-width:250px"/>'));
                    } else {
                        $response->addCommand(new ReplaceCommand('#thumb-'.$id, '<img class="w3-image" src="' .$thumb.'" typeof="Image"/>'));
                    }
                }
            }
        }

        //$response->addCommand(new InvokeCommand(null, 'changeDatesCallback', [$form_state->getValues()]));
        //$response->addCommand(new ReplaceCommand('#thumb-'.$id, '<img class="w3-image" src="' .$thumb.'" typeof="Image"/>"'));

        return $response;
    }
}

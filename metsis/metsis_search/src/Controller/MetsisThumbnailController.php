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
use Drupal\Core\Ajax\RemoveCommand;

class MetsisThumbnailController extends ControllerBase
{
    //Querey the document for the given id and return the thumbnail.
    public function loadThumbnails($id)
    {

      /** @var Index $index  TODO: Change to metsis when prepeare for release */
        $index = Index::load('metsis');

        /** @var SearchApiSolrBackend $backend */
        $backend = $index->getServerInstance()->getBackend();

        //Get the solr connector for backend and index
        $connector = $backend->getSolrConnector();

        //Get the select query handler
        $solarium_query = $connector->getSelectQuery();
        //Modify the query
        $solarium_query->setQuery('id:'.$id);
        $solarium_query->setRows(1);
        $solarium_query->setFields('thumbnail_data');

        //Execute the query
        $result = $connector->execute($solarium_query);

        // The total number of documents found by Solr.
        $found = $result->getNumFound();

        //$thumb = '/modules/metsis/metsis_search/images/missing_map_place_holder.png';
        $thumb = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        $response = new AjaxResponse();
        //Return thumbnail inside image tag if document have thumbnail_data. If not remove the thumbnail wrapper <div>.
        if ($found > 0) {
            foreach ($result as $doc) {
                if (sizeof($doc->getFields()) > 0) {
                    foreach ($doc as $field => $value) {
                        if ($field === 'thumbnail_data') {
                            $thumb = $value;
                            //dpm('got thumb for id:'.$id.': ' . $thumb);
                            //Add thumbnail image tag
                            $response->addCommand(new ReplaceCommand('#thumb-'.$id, '<img class="w3-image" src="' .$thumb.'" typeof="Image" style="width:70%;max-width:250px"/>'));
                        }
                    }
                } else {
                    //dpm('using no thumb for id: ' . $id);
                    //Remove wrapper tag
                    $response->addCommand(new RemoveCommand('#thumb-wrapper-'.$id));
                }
            }
        }

        //Return ajax response
        return $response;
    }
}
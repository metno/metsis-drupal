<?php
/**
 * @file
 * Contains \Drupal\metsis_basket\Controller\MetsisBasketListingController.
 */

namespace Drupal\metsis_basket\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\metsis_lib\MetsisUtils;
use Drupal\metsis_basket\Entity\BasketItem;
use Drupal\Core\Entity\Controller\EntityListController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\MessageCommand;

use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Select\Result\Document;
use Solarium\Core\Query\DocumentInterface;
use Solarium\Core\Query\Result\ResultInterface;


/**
 * Default controller for the metsis_basket module.
 * {@inheritdoc}
 */
class MetsisBasketController extends ControllerBase  {

  public function myBasket() {
    //Get the user_id
    $user_id = (int) \Drupal::currentUser()->id();

    //Get the refering page
    $session = \Drupal::request()->getSession();
    $referer = $session->get('back_to_search');


    //Create content wrapper
    $build['content'] = [
      '#prefix' => '<div class="w3-container">',
      '#suffix' => '</div>'
    ];


    $build['content']['back'] = [
      '#markup' => '<a class="w3-btn" href="'. $referer . '">Go back to search </a>',
    ];
    //$build['content']['dashboard'] = [
    //  '#markup' => '<a class="w3-btn" href="/metsis/bokeh/dashboard">Go to Dashboard (GET)</a>',
    //];
    $build['content']['dashboard-post'] = [
      '#markup' => '<a class="w3-btn" href="/metsis/bokeh/dashboard/post">Go to Dashboard</a>',
    ];

    $build['content']['view'] = views_embed_view('basket_view', 'embed_1');

    $build['#cache'] = [

      'max-age' => 0,
    ];

    $build['#attached'] = [
'library' => [
'metsis_basket/basket_view',
],
];

    return $build;
  }

  public function listing($iid) {
    \Drupal::logger('metsis_basket')->debug("Listing item with iid: " . $iid);
    //$objects = \Drupal::entityTypeManager()->getStorage('metsis_basket', array($iid));


    //$objects = MetsisBasket::load($iid);
    //$mb = $objects[$iid];
    // @FIXME
    // drupal_set_title() has been removed. There are now a few ways to set the title
    // dynamically, depending on the situation.
    //
    //
    // @see https://www.drupal.org/node/2067859
    // drupal_set_title($mb->name);
    $view_builder = \Drupal::entityTypeManager()
      ->getViewBuilder('metsis_basket_item');
    $entity = \Drupal::entityTypeManager()
      ->getStorage('metsis_basket_item')->load($iid);
      //\Drupal::logger('metsis_basket')->debug("Loaded entity with iid: " . $entity->id());
    return $view_builder
      ->view($entity, 'full');


  }

  public function add($metaid) {
    \Drupal::logger('metsis_basket')->debug("Calling add to basket function");
    $user_id = (int) \Drupal::currentUser()->id();
    $user_name = \Drupal::currentUser()->getAccountName();

    //Generate uuid from uuid service
    $uuid_service = \Drupal::service('uuid');
    $uuid = $uuid_service->generate();
    //Get info from solr given metaid that we put in the basket
    $arr  = $this->msb_get_resources($metaid);



    $feature_type = $arr[0];
    $title = $arr[1];
    $dar = $arr[2];



    \Drupal::logger('metsis_basket')->debug("Adding product to basket:");
    \Drupal::logger('metsis_basket')->debug("title: @title", ['@title'  => $title]);
    \Drupal::logger('metsis_basket')->debug("feature_type: @ft", ['@ft'  => $feature_type]);
    \Drupal::logger('metsis_basket')->debug("dar: @ft", ['@ft'  => Json::encode($dar)]);



    //Te fields we put in database
      $fields = [
        'uid' => $user_id,
        'uuid' => $uuid,
        'user_name' => $user_name,
        'title' => $title,
        'session_id' => session_id(),
        'basket_timestamp' => time(),
        'metadata_identifier' => $metaid,
        'feature_type' => $feature_type,
        'dar' => serialize($dar),
      ];
      //dpm($res);
      $query = \Drupal::database()->insert('metsis_basket')->fields($fields)->execute();




    //\Drupal::logger('metsis_basket')->debug("dashboard json: " . $dashboard_json);

    $basket_count = $this->get_user_item_count($user_id);
    $ids = $this->get_user_item_ids($user_id);
    //\Drupal::logger('metsis_basket')->debug(implode(',',$ids));

    $tempstore = \Drupal::service('user.private_tempstore')->get('metsis_basket');
    $tempstore->set('basket_items', $ids);


    $selector = '#myBasketCount';
    //$markup = '<a href="/metsis/elements?metadata_identifier="'. $id .'"/>Child data..['. $found .']</a>';

    $markup = '<span id="myBasketCount" class="w3-badge w3-green">' . $basket_count . '</span>';

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#addtobasket-' . $metaid ,'Add to Basket &#10004;'));
    $response->addCommand(new HtmlCommand($selector,$markup));
    //$response->addCommand(new MessageCommand("Dataset added to basket:  " . $metaid));

    return $response;
  }

  public static  function get_user_item_count($user_id) {
    $query = \Drupal::database()->select('metsis_basket', 'm');
    $query->fields('m', array('iid'));
    $query->condition('m.uid', $user_id, '=');
    $results = $query->execute()->fetchAll();
    return count($results);
  }

  public static  function get_user_item_ids($user_id) {
    $query = \Drupal::database()->select('metsis_basket', 'm');
    $query->fields('m', array('metadata_identifier'));
    $query->condition('m.uid', $user_id, '=');
    $results = $query->execute()->fetchCol();
    return $results;
  }


  public static function msb_get_resources($metadata_identifier)
  {
      /** @var Index $index  TODO: Change to metsis when prepeare for release */
      $index = Index::load('metsis');

      /** @var SearchApiSolrBackend $backend */
      $backend = $index->getServerInstance()->getBackend();

      $connector = $backend->getSolrConnector();

      $solarium_query = $connector->getSelectQuery();


      \Drupal::logger('metsis_basket_solr_query')->debug("metadata_identifier: " .$metadata_identifier);
      $solarium_query->setQuery('metadata_identifier:'.$metadata_identifier);

      //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
      //$solarium_query->setRows(2);
      $solarium_query->setFields([
        'data_access_url_http',
        'data_access_url_ftp',
        'data_access_url_odata',
        'data_access_url_opendap',
        'data_access_url_ogc_wms',
        'feature_type',
        'title',
      ]);

      $result = $connector->execute($solarium_query);

      // The total number of documents found by Solr.
      $found = $result->getNumFound();
      \Drupal::logger('metsis_basket_solr_query')->debug("found :" .$found);
      // The total number of documents returned from the query.
      //$count = $result->count();

      // Check the Solr response status (not the HTTP status).
      // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
      //$status = $result->getStatus();
      $title = 'NA';
      $feature_type = 'NA';
      $dar = [];
      foreach ($result as $doc) {
        $fields = $doc->getFields();

      }
      if(isset($fields['data_access_url_http'])) {
        // An array of documents. Can also iterate directly on $result.
        $dar['http'] = $fields['data_access_url_http'];
      }
      if(isset($fields['data_access_url_ftp'])) {
        // An array of documents. Can also iterate directly on $result.
        $dar['http'] = $fields['data_access_url_ftp'];
      }
      if(isset($fields['data_access_url_odata'])) {
        // An array of documents. Can also iterate directly on $result.
        $dar['odata'] = $fields['data_access_url_odata'];
      }
      if(isset($fields['data_access_url_opendap'])) {
        // An array of documents. Can also iterate directly on $result.
        $dar['opendap'] = $fields['data_access_url_opendap'];
      }
      if(isset($fields['data_access_url_ogc_wms'])) {
        // An array of documents. Can also iterate directly on $result.
        $dar['ogc_wms'] = $fields['data_access_url_ogc_wms'];
      }
      if(isset($fields['feature_type'])) {
        // An array of documents. Can also iterate directly on $result.
        $feature_type = $fields['feature_type'];

      }
      if(isset($fields['title'])) {
        // An array of documents. Can also iterate directly on $result.
        $title = $fields['title'][0];
      }
        return array($feature_type, $title, $dar);
    }
}

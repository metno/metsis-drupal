<?php

namespace Drupal\metsis_search\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
// Use Drupal\devel\DevelDumperManagerInterface;.
use Drupal\search_api\LoggerTrait;
use Solarium\Core\Event\Events as SolariumEvents;
use Solarium\Core\Event\PostCreateQuery;
use Solarium\Core\Event\PostExecuteRequest;
use Solarium\Core\Event\PreExecuteRequest;
use Solarium\Core\Event\PostCreateResult;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\Core\Cache\CacheBackendInterface;

use Drupal\metsis_search\SearchUtils;

/**
 * Event subscriber for listening to search api events and solr evnets.
 */
class MetsisSearchEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;
  use LoggerTrait;
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Current session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Cache backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Special solr chars.
   *
   * @var array
   */
  protected $specialChars = ['*', '?', ':'];

  /**
   * To hold the current searchId.
   *
   * @var string
   */
  protected $searchId;


  /**
   * Default solr search fields needed for metsis_search.
   *
   * @var array
   */
  protected $defaultFields = [
    'id',
    'personnel_organisation',
    'project_long_name',
    'project_short_name',
    'temporal_extent_start_date',
    'temporal_extent_end_date',
    'last_metadata_update_datetime',
     // 'abstract',
    'related_url_landing_page',
     // 'thumbnail_data',
    'isParent',
    'data_access_url_opendap',
    'feature_type',
    'ss_access',
    'data_access_url_http',
    'data_access_url_odata',
    'uuid',
    'score',
    'hash',
    'geographic_extent_rectangle_south',
    'geographic_extent_rectangle_north',
    'geographic_extent_rectangle_west',
    'geographic_extent_rectangle_east',
    'use_constraint',
     // 'iso_topic_category',
    'activity_type',
    'dataset_production_status',
    'metadata_status',
     // 'data_center_long_name',
     // 'data_center_short_name',
     // 'data_center_url',
     // 'personnel_datacenter_role',
     // 'personnel_datacenter_name',
     // 'personnel_datacenter_email',
    'personnel_name',
    'metadata_identifier',
    'collection',
     // 'keywords_keyword',
    'data_access_url_ftp',
    'data_access_url_ogc_wms',
    'data_access_wms_layers',
  ];

  /**
   * Construct an example service instance.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Account proxy for the currently logged-in user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The metsis search config.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The current session.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   */
  public function __construct(
        AccountProxyInterface $current_user,
        ConfigFactoryInterface $configFactory,
        SessionInterface $session,
        CacheBackendInterface $cache
    ) {
    $this->currentUser = $current_user;
    $this->config = $configFactory->get('metsis_search.settings');
    $this->session = $session;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[SolariumEvents::POST_CREATE_QUERY][] = ['postCreateQuery'];
    $events[SolariumEvents::PRE_EXECUTE_REQUEST][] = ['preExecuteRequest'];
    $events[SolariumEvents::POST_EXECUTE_REQUEST][] = ['postExecuteRequest'];
    $events[SolariumEvents::POST_CREATE_RESULT][] = ['postCreateResult'];
    $events[SearchApiSolrEvents::PRE_QUERY][] = ['onPreQuery'];
    $events[SearchApiSolrEvents::POST_CONVERT_QUERY][] = ['postConvertQuery'];
    $events[SearchApiSolrEvents::POST_EXTRACT_RESULTS][] = ['postExtractResults'];
    return $events;
  }

  /**
   * Listen to  the post convert query event.
   *
   * @param \Drupal\search_api_solr\Event\PostConvertedQueryEvent $event
   *   The current event.
   */
  public function postConvertQuery(PostConvertedQueryEvent $event) {

  }

  /**
   * Listen to  the pre create query.
   *
   * @param \Drupal\search_api_solr\Event\PreQueryEvent $event
   *   The current event.
   */
  public function onPreQuery(PreQueryEvent $event) {
    // Search api query.
    $query = $event->getSearchApiQuery();
    // Solarium search api solr query.
    $solarium_query = $event->getSolariumQuery();

    // Get the search id for this search view.
    $searchId = $query->getSearchId();
    $this->searchId = $searchId;
    // Only do something during this event if we have metsis search view.
    if (($searchId !== NULL) && ($searchId === 'views_page:metsis_search__results')) {
      // dpm('Got metsis search query...');.
      /*
       * Invalidate the search result map cache
       */
      $this->cache->invalidate('metsis_search_map');
      // Get the current request object.
      $request = \Drupal::request();

      if ($request->headers->has('referer')) {
        $this->session->set('back_to_search', $request->headers->get('referer'));
      }
      if ($this->session->has('bboxFilter')) {
        $bboxFilter = $this->session->get('bboxFilter');
      }
      else {
        $bboxFilter = NULL;
      }
      // Get filter predicate from config.
      if ($this->session->has('cond')) {
        $map_bbox_filter = ucfirst($this->session->get('cond'));
      }
      else {
        $map_bbox_filter = $this->config->get('map_bbox_filter');
      }
      if ($this->session->get("place_filter") === 'Contains') {
        $map_bbox_filter = 'Contains';
      }

      // Add bbox filter query if drawn bbox on map.
      if ($bboxFilter != NULL && $bboxFilter != "") {
        \Drupal::logger('metsis_search-hook_solr_qyery_alter')->debug("bboxFilter: " . $map_bbox_filter . '(' . $bboxFilter . ')');
        $solarium_query->createFilterQuery('bbox')->setQuery('{!field f=bbox score=overlapRatio}' . $map_bbox_filter . '(' . $bboxFilter . ')');
        $search_string = $map_bbox_filter . '(' . $bboxFilter . ')';
        // $request->query->set('bboxFilter', $search_string);
        // $request->request->set('bboxFilter', $search_string);
      }

      // Filter on selected collections from config.
      $selected_collections = $this->config->get('selected_collections');
      if (isset($selected_collections) && $selected_collections != NULL) {
        // \Drupal::logger('metsis_search-hook_solr_qyery_alter')->debug("collections filter: " .implode(" ", array_keys($selected_collections)));
        $solarium_query->createFilterQuery('collection')->setQuery('collection:(' . implode(" ", array_keys($selected_collections)) . ')');
      }
      /*
       * Parent boost results
       */
      $score_parent = $this->config->get('score_parent');
      if ($score_parent) {
        $def_sorts = $solarium_query->getSorts();
        /*
        if(isset($def_sorts['temporal_extent_start_date'])) {

        }*/
        $solarium_query->clearSorts();
        $solarium_query->addSort('score', $solarium_query::SORT_DESC);
        foreach ($def_sorts as $field => $order) {
          $solarium_query->addSort($field, $order);
        }

        // dpm($solarium_query->getSorts());
        $solarium_query->addParam('rq', '{!rerank reRankQuery=(isParent:true) reRankDocs=1000 reRankWeight=5}');
      }
      /*
       * Add fields not defined in search view but needed for
       * other metsis search backends. I.E MapSearch
       */
      $fields = $solarium_query->getFields();
      $newfields = array_merge($fields, $this->defaultFields);
      // Make sure the fields array contains unique fields.
      $uniq_fields = array_unique($newfields);

      /*
       *  Hack to avoid users to have to use "" when searching for identifiers
       * with nameing authrity prefixes.
       */
      $keys = $query->getKeys();
      // dpm($keys);
      if ($keys != NULL) {
        if (substr($keys[0], 0, 6) === 'no.met') {
          $new_keys = str_replace(':', '?', $keys[0]);
          $query->keys($new_keys);
          // dpm($query->getKeys());
        }
        if (substr($keys[0], 0, 8) === 'no.nersc') {
          $new_keys = str_replace(':', '?', $keys[0]);
          $query->keys($new_keys);
          // dpm($query->getKeys());
        }
      }

      $solarium_query->setFields($uniq_fields);
      // We dont need to get the thumbnail data as we will lazy-load that later.
      $solarium_query->removeField('thumbnail_data');
      /*
       * Manipulate the parse mode for the query
       */

      // Get parsemode plugin interface.
      $parse_mode_service = \Drupal::service('plugin.manager.search_api.parse_mode');

      $keys = $query->getKeys();
      // Use direct query?
      $use_direct = FALSE;
      if ($keys !== NULL) {
        // dpm($keys);
        foreach ($keys as $key => $value) {
          if (!is_array($value)) {
            if (preg_match('/[' . preg_quote(implode(',', $this->specialChars)) . ']+/', $value)) {
              $use_direct = TRUE;
            }
          }
          else {
            foreach ($value as $key => $value2) {
              if (preg_match('/[' . preg_quote(implode(',', $this->specialChars)) . ']+/', $value2)) {
                $use_direct = TRUE;
              }
            }
          }
        }
      }
      $conjuction = $query->getParseMode()->getConjunction();
      if ($use_direct) {
        $parse_mode = $parse_mode_service->createInstance('direct');
        $parse_mode->setConjunction($conjuction);
        $query->setParseMode($parse_mode);
      }
      //
      // dpm($query->getParseMode()->label());
      // dpm($this->config);
      // dpm($this->session);
      // dpm($solarium_query->getFields());
    }
  }

  /**
   * Listen to  the post create query.
   *
   * @param \Drupal\search_api_solr\Event\PostExtractResultsEvent $event
   *
   *   the current Event.
   */
  public function postExtractResults(PostExtractResultsEvent $event) {

  }

  /**
   * Listen to  the post create query.
   *
   * @param \Solarium\Core\Event\PostCreateQuery $event
   *   the current Event.
   */
  public function postCreateQuery(PostCreateQuery $event) {
  }

  /**
   * Listen to the pre execute query  event.
   *
   * @param \Solarium\Core\Event\PreExecuteRequest $event
   *   The pre execute event.
   */
  public function preExecuteRequest(PreExecuteRequest $event) {
    // \Drupal::logger('metsis-search')->debug("PreExecuteRequest");
  }

  /**
   * Listen to the post execute query event.
   *
   * @param \Solarium\Core\Event\PostExecuteRequest $event
   *   The post execute event.
   */
  public function postExecuteRequest(PostExecuteRequest $event) {
    // \Drupal::logger('metsis-search')->debug("PostExecuteRequest");
  }

  /**
   * Listen to the post create result event.
   *
   * @param \Solarium\Core\Event\PostCreateResult $event
   *   The post create result event.
   */
  public function postCreateResult(PostCreateResult $event) {
    // \Drupal::logger('metsis-search')->debug("postCreateResult");
    // dpm($event->getResult());
    if (($this->searchId !== NULL) && ($this->searchId === 'views_page:metsis_search__results')) {
      // dpm($this->searchId);
      // $result = $event->getSearchApiResultSet();
      $extracted_info = SearchUtils::getExtractedInfo($event->getResult());
      // \Drupal::logger('metsis_search-hook_search_results')->debug('<pre><code>' . print_r($event->getResult(), true) . '</code></pre>');
      // \Drupal::logger('metsis_search-hook_search_extracted_info')->debug('<pre><code>' . print_r($extracted_info, true) . '</code></pre>');
      $this->session->set('extracted_info', $extracted_info);
      if ($this->session->has('basket_ref')) {
        $this->session->remove('basket_ref');
      }
    }
  }

}

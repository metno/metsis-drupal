<?php

namespace Drupal\metsis_search\EventSubscriber;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\metsis_search\MetsisSearchState;
use Drupal\metsis_search\SearchUtils;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Event\QueryPreExecuteEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api\LoggerTrait;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\search_api\Query\Condition;
use Drupal\search_api\Query\ConditionGroup;
use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;
use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Solarium\Core\Event\Events as SolariumEvents;
use Solarium\Core\Event\PostCreateQuery;
use Solarium\Core\Event\PostCreateRequest;
use Solarium\Core\Event\PostCreateResult;
use Solarium\Core\Event\PostExecuteRequest;
use Solarium\Core\Event\PreCreateQuery;
use Solarium\Core\Event\PreCreateRequest;
use Solarium\Core\Event\PreExecuteRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $requestStack;

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
  protected $specialChars = ['*', '?', ':', '!'];

  /**
   * To hold the current searchId.
   *
   * @var string
   */
  protected $searchId;

  /**
   * To hold the current extracted map info.
   *
   * @var string
   */
  protected $mapInfo;

  /**
   * MetsisSearchState service for holding data between events during request.
   *
   * @var array
   */
  protected $metsisState;

  /**
   * Parse mode plugin manager service.
   *
   * @var \Drupal\search_api\ParseMode\ParseModePluginManager
   */
  protected $parseModeService;

  /**
   * UUID Regexp pattern.
   *
   * @var string
   */
  protected $uuidRegexp = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

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
    'total_children:[subquery]',
    'found_children:[subquery]',
    'parent:[subquery]',
  ];

  /**
   * Query string for adding open end date to date range search.
   *
   * @var string
   */
  protected $openEndDateQuery = 'NOT temporal_extent_end_date:[* TO *]';

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
   * @param \Symfony\Component\HttpFoundation\Request $requestStack
   *   The current request stack.
   * @param \Drupal\metsis_search\MetsisSearchState $state
   *   The metsisSearch state service.
   * @param \Drupal\search_api\ParseMode\ParseModePluginManager $parse_mode_service
   *   The parse mode plugin manager service.
   */
  public function __construct(
    AccountProxyInterface $current_user,
    ConfigFactoryInterface $configFactory,
    SessionInterface $session,
    CacheBackendInterface $cache,
    RequestStack $requestStack,
    MetsisSearchState $state,
    ParseModePluginManager $parse_mode_service,
  ) {
    $this->currentUser = $current_user;
    $this->config = $configFactory->get('metsis_search.settings');
    $this->session = $session;
    $this->cache = $cache;
    $this->requestStack = $requestStack;
    $this->metsisState = $state;
    $this->parseModeService = $parse_mode_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[SolariumEvents::PRE_CREATE_QUERY][] = ['preCreateQuery'];
    $events[SolariumEvents::POST_CREATE_QUERY][] = ['postCreateQuery'];
    $events[SolariumEvents::PRE_EXECUTE_REQUEST][] = ['preExecuteRequest'];
    $events[SolariumEvents::PRE_CREATE_REQUEST][] = ['preCreateRequest'];
    $events[SolariumEvents::POST_CREATE_REQUEST][] = ['postCreateRequest'];
    $events[SolariumEvents::POST_EXECUTE_REQUEST][] = ['postExecuteRequest'];
    $events[SolariumEvents::POST_CREATE_RESULT][] = ['postCreateResult'];
    $events[SearchApiSolrEvents::PRE_QUERY][] = ['onPreQuery'];
    $events[SearchApiSolrEvents::POST_CONVERT_QUERY][] = ['postConvertQuery'];
    $events[SearchApiSolrEvents::POST_EXTRACT_RESULTS][] = ['postExtractResults'];
    $events[SearchApiEvents::QUERY_PRE_EXECUTE][] = ['queryPreExecute'];
    return $events;
  }

  /**
   * Listen to  the post create query.
   *
   * @param \Solarium\Core\Event\PreCreateQuery $event
   *   the current Event.
   */
  public function preCreateQuery(PreCreateQuery $event): void {
    // $this->getLogger()->notice('preCreateQuery');
    // dpm($event, __FUNCTION__);.
  }

  /**
   * Listen to the search api pre query execute event.
   *
   * @param \Drupal\search_api\Event\QueryPreExecuteEvent $event
   *   the current Event.
   */
  public function queryPreExecute(QueryPreExecuteEvent $event): void {
    $this->getLogger()->notice('queryPreExecute');
    $query = $event->getQuery();
    $searchId = $query->getSearchId();
    $this->searchId = $searchId;

    // Check if we have a bbox query filter,
    // and store in our metsisState service.
    // Also if we have related_dataset_id (parent filter)
    // We will need to update the isParent/isChild contions.
    $conditions = $query->getConditionGroup()->getConditions();
    // dpm($conditions, __FUNCTION__);.
    $got_parent_filter = FALSE;
    foreach ($conditions as $condition) {
      if ($condition instanceof ConditionGroup) {
        $conds = $condition->getConditions();
        foreach ($conds as $cond) {
          // Check if the condition is a filter.
          if ($cond instanceof Condition) {
            // Get the field name and value of the filter.
            $fieldName = $cond->getField();
            if ($fieldName === 'bbox') {
              $value = $cond->getValue();
              $operator = $cond->getOperator();
              $this->metsisState->set('bbox_filter', $value);
              $this->metsisState->set('bbox_op', $operator);
            }
            if ($fieldName === 'related_dataset_id') {
              // dpm("Got parent filter", __LINE__);.
              $got_parent_filter = TRUE;
            }
          }
        }
      }
      else {
        if ($condition instanceof Condition) {
          $fieldName = $condition->getField();
          if ($fieldName === 'bbox') {
            $value = $condition->getValue();
            $operator = $condition->getOperator();
            $this->metsisState->set('bbox_filter', $value);
            $this->metsisState->set('bbox_op', $operator);
          }
        }
        if ($fieldName === 'related_dataset_id') {
          // dpm("Got parent filter", __LINE__);.
          $got_parent_filter = TRUE;
        }
      }
    }
    // If parent_fiter was found, we wil need to loop again
    // and updated the isChild filter from false to true.
    if ($got_parent_filter == TRUE) {
      foreach ($conditions as $condition) {
        if ($condition instanceof ConditionGroup) {
          $conds = $condition->getConditions();
          foreach ($conds as $cond) {
            // Check if the condition is a filter.
            if ($cond instanceof Condition) {
              // Get the field name and value of the filter.
              $fieldName = $cond->getField();
              if ($fieldName === 'is_child') {
                $cond->setValue(1);
              }
            }
          }
        }
        else {
          if ($condition instanceof Condition) {
            $fieldName = $condition->getField();
            if ($fieldName === 'is_child') {
              $got_parent_filter = TRUE;
            }
          }
        }
      }
    }
    // dpm($conditions, __FUNCTION__);.

  }

  /**
   * Listen to  the post convert query event.
   *
   * Here we can modify the solr q parameter.
   *
   * Here we also make child join queries, for searching for child documents.
   * Also a subquery for getting the total child counti is made here.
   *
   * @param \Drupal\search_api_solr\Event\PostConvertedQueryEvent $event
   *   The current event.
   */
  public function postConvertQuery(PostConvertedQueryEvent $event): void {
    $this->getLogger()->notice('postConvertQuery');

    // Search api query.
    $query = $event->getSearchApiQuery();
    // Solarium search api solr query.
    $solarium_query = $event->getSolariumQuery();

    // Get the Query Helper from the Solarium API.
    $helper = $solarium_query->getHelper();

    // Get the search id for this search view.
    $searchId = $query->getSearchId();
    // Store the search id in this instance so we can use it later.
    $this->searchId = $searchId;

    // Only execute for special metsis search views.
    if (($searchId !== NULL) && ($searchId === 'views_page:metsis_search__results')) {
      // Get the current query object as it is now after
      // being converted by search api.
      $current_query = $solarium_query->getQuery();

      // dpm($current_query, __FUNCTION__);
      // $current_query = str_replace(')', '', $current_query);
      // $current_query = str_replace('(', '', $current_query);
      // . ' OR ' .

      // Fix a bit on the current main query string q=.
      $main_query = $helper->escapePhrase($current_query);
      $main_query = rtrim($main_query, '"');
      $main_query = ltrim($main_query, '"');
      // dpm($main_query, __FUNCTION__);.

      /*
       * Add join query for querying the child datasets as well, when
       * main query only search Level-1 datasets.
       */
      $do_child_join = $this->config->get('search_match_children');
      if ($do_child_join) {
        $solarium_query->setQuery($main_query . ' OR _query_:"' . $helper->join('related_dataset_id', 'id') . $main_query . '"');
      }

      /*
       * Create subquery for filtering the child datasets and to get the number
       * of children found using the filters from the main_query.
       */

      // Add the main query q= to the child query.
      $solarium_query->addParam('found_children.q', $main_query);

      // Get the filters from the main query.
      $filters = $solarium_query->getFilterQueries();

      // dpm($filters, __FUNCTION__);.

      // Array to hold the filters for the child query.
      $child_query_filters = [];

      // Add the same collection filter as from the main query.
      // $child_query_filters[] = $filters['collection']->getOption('query');.

      // Add bbox filter if exits in main query.
      // And to the metsis state for bbox filter.
      // dpm($filters);

      // Some helpers.
      $pattern = '/\+\w+_dataset_id:"[^"]+"/';
      foreach ($filters as $filter) {
        if ($filter->getOption('query') === '(isParent:"true" isParent:"false")') {
          continue;
        }
        elseif (strpos($filter->getOption('query'), '+isChild') !== FALSE) {
          $fq = str_replace('+isChild:"false"', '+isChild:"true"', $filter->getOption('query'));

          if (preg_match($pattern, $filter->getOption('query'), $m) == 1) {
            $fq = preg_replace($pattern, '', $fq);
          }
          $child_query_filters[] = $fq;
        }
        elseif (preg_match($pattern, $filter->getOption('query'), $m) == 1) {
          $fq = preg_replace($pattern, '', $filter->getOption('query'));
          $child_query_filters[] = $fq;
        }

        else {
          $child_query_filters[] = $filter->getOption('query');
        }

      }
      // Filter on related children.
      $child_query_filters[] = '{!terms f=related_dataset_id v=$row.id}';

      // Add the filters to the child query.
      // dpm($child_query_filters);
      $solarium_query->addParam('found_children.fq', $child_query_filters);

      // We don't want to return any child documents at this point.
      // @todo Something This could be implemented at a lter point.
      $solarium_query->addParam('found_children.rows', '0');

      /*
       * Create subquery for getting the total number of children for each
       * parent document in the main query result set.
       */
      $solarium_query->addParam('total_children.q', '{!terms f=related_dataset_id v=$row.id}');
      $solarium_query->addParam('total_children.rows', '0');

      /*
       * Add parent subquery to get information about parent
       * if dataset is a child.
       */
      $solarium_query->addParam('parent.q', '{!terms f=id v=$row.related_dataset_id}');
      $solarium_query->addParam('parent.fq', 'isParent:"true"');
      $solarium_query->addParam('parent.rows', '1');
      $solarium_query->addParam('parent.fl', 'id,metadata_identifier,title, abstract, related_url_landing*,temporal*date*');
    }
  }

  /**
   * Listen to  the pre create query.
   *
   * @param \Drupal\search_api_solr\Event\PreQueryEvent $event
   *   The current event.
   */
  public function onPreQuery(PreQueryEvent $event): void {
    $this->getLogger()->notice('onPreQuery');

    // Search api query.
    $query = $event->getSearchApiQuery();
    // Solarium search api solr query.
    $solarium_query = $event->getSolariumQuery();
    // $current_query = $solarium_query->getQuery();
    // dpm($current_query, __FUNCTION__);.
    // Set t full_text as the default search field.
    $solarium_query->setQueryDefaultField('full_text');
    // Get and set the searchid to be used in other methods.
    $searchId = $query->getSearchId();
    $this->searchId = $searchId;

    // Handeling simple search view.
    if (($searchId !== NULL) && (($searchId === 'views_page:metsis_simple_search__results'))) {
      $conditions = $query->getConditionGroup()->getConditions();
      $range_op = NULL;
      if (isset($conditions[3])) {
        $range_op = $conditions[3]->getConditions()[0]->getOperator();
        // dpm($range_op, __LINE__);.
        $solarium_query->removeFilterQuery('filters_3');
      }
      if ($range_op != NULL and $range_op != 'Between') {
        // dpm($solarium_query, __FUNCTION__);.
        $range_filter = $solarium_query->getFilterQuery('filters_2');
        $range_filter_query = $range_filter->getQuery();
        $qf = explode(':', $range_filter_query, 2);
        $field = $qf[0];
        $dr = preg_replace('/(T[0-9]{2}:[0-9]{2}:[0-9]{2}Z)/', '', $qf[1]);
        $dr = str_replace('"', '', $dr);
        $solarium_query->removeFilterQuery('filters_2');
        $options = [
          'key' => 'filters_2',
          'query' => "{!field f=$field op=$range_op}$dr",
        ];
        // dpm($options);
        $solarium_query->addFilterQuery($options);
      }
    }

    // Only do something during this event if we have metsis search view.
    if (($searchId !== NULL) && (($searchId === 'views_page:metsis_search__results')
      || $this->searchId === 'views_page:metsis_elements__results' || $this->searchId === 'views_page:metsis_simple_search__results'
     || $this->searchId === 'views_page:metsis_search_date_test__results')) {
      // dpm('Got metsis search query...');.
      // dpm("Before");
      // dpm($query);
      // dpm($solarium_query);
      // dpm($query->getConditionGroup()->getConditions());
      /*
       * Invalidate the search result map cache
       */
      $this->cache->invalidate('metsis_search_map');
      if ($this->requestStack->getCurrentRequest()->headers->has('referer')) {
        $this->session->set('back_to_search', $this->requestStack->getCurrentRequest()->headers->get('referer'));
      }
      // If ($this->session->has('bboxFilter')) {
      // $bboxFilter = $this->session->get('bboxFilter');
      // }
      // else {
      // $bboxFilter = NULL;
      // }
      // // Get filter predicate from config.
      // if ($this->session->has('cond')) {
      // $map_bbox_filter = ucfirst($this->session->get('cond'));
      // }
      // else {
      // $map_bbox_filter = $this->config->get('map_bbox_filter');
      // }
      // if ($this->session->get("place_filter") === 'Contains') {
      // $map_bbox_filter = 'Contains';
      // }.
      // // Add bbox filter query if drawn bbox on map.
      // $bbox_filter_overlap = $this->config->get('bbox_overlap_sort');
      // if ($bboxFilter != NULL && $bboxFilter != "") {
      // $this->getLogger('metsis_search-hook_solr_query_alter')->debug("bboxFilter: " . $map_bbox_filter . '(' . $bboxFilter . ')');
      // if ($bbox_filter_overlap) {
      // $solarium_query->createFilterQuery('bbox')->setQuery('{!field f=bbox score=overlapRatio}' . $map_bbox_filter . '(' . $bboxFilter . ')');
      // }
      // else {
      // $solarium_query->createFilterQuery('bbox')->setQuery('{!field f=bbox}' . $map_bbox_filter . '(' . $bboxFilter . ')');
      // }
      // $search_string = $map_bbox_filter . '(' . $bboxFilter . ')';
      // $request->query->set('bboxFilter', $search_string);
      // $request->request->set('bboxFilter', $search_string);
      // }.

      // Filter on selected collections from config.
      $selected_collections = $this->config->get('selected_collections');
      if (isset($selected_collections) && $selected_collections != NULL) {
        $solarium_query->createFilterQuery('collection')->setQuery('collection:(' . implode(" ", array_keys($selected_collections)) . ')');
      }

      /*
       * We always want to sort by score, then date if its a tie.
       */
      $def_sorts = $solarium_query->getSorts();
      $solarium_query->clearSorts();
      $solarium_query->addSort('score', $solarium_query::SORT_DESC);
      foreach ($def_sorts as $field => $order) {
        $solarium_query->addSort($field, $order);
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
          if (str_contains($field, "temporal")) {
            // dpm($field);
            // $solarium_query->addParam('bf', "recip(abs(ms(NOW, {$field})),
            // 3.16e-11,10,0.1)");.
          }
        }

        // dpm($solarium_query->getSorts());
        $solarium_query->addParam('rq', '{!rerank reRankQuery=(isParent:true) reRankDocs=1000 reRankWeight=5}');
      }

      /*
       * Score start date test.
       */

      /*
       * New score parents test.
       *
       * TODO: Add config posibility if this works well.
       */
      $solarium_query->addParam('bq',
      [
        'iParent' =>
        '(isParent:true^4 OR isParent:false^2)',
        'isChild' => 'isChild:true^1',
      ]);
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
        if (is_string($keys[0])) {
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
          if ($this->isValidUuid($keys[0])) {
            $new_keys = '*' . $keys[0];
            $query->keys($new_keys);
          }
        }
      }
      // dpm($query->getKeys());
      $solarium_query->setFields($uniq_fields);
      // We dont need to get the thumbnail data as we will lazy-load that later.
      $solarium_query->removeField('thumbnail_data');
      /*
       * Manipulate the parse mode for the query
       */
      $keys = $query->getKeys();
      // Use direct query?
      $use_direct = FALSE;
      if ($keys !== NULL) {
        if (is_array($keys)) {
          // dpm($keys);
          foreach ($keys as $value) {
            if (!is_array($value)) {
              if (preg_match('/[' . preg_quote(implode(',', $this->specialChars)) . ']+/', $value)) {
                $use_direct = TRUE;
              }
              if ($this->isValidUuid($value)) {
                $use_direct = TRUE;
              }
            }

            else {
              foreach ($value as $value2) {
                // @phpcs-ignore UnusedLocalVariable
                if (preg_match('/[' . preg_quote(implode(',', $this->specialChars)) . ']+/', $value2)) {
                  $use_direct = TRUE;
                }
                if ($this->isValidUuid($value2)) {
                  $use_direct = TRUE;
                }
              }
            }
          }
        }
        else {
          if (preg_match('/[' . preg_quote(implode(',', $this->specialChars)) . ']+/', $keys)) {
            $use_direct = TRUE;
          }
        }
      }
      $conjuction = $query->getParseMode()->getConjunction();
      if ($use_direct) {
        $parse_mode = $this->parseModeService->createInstance('direct');
        $parse_mode->setConjunction($conjuction);
        $query->setParseMode($parse_mode);
      }
      /* Rewrite the query for when end date filter is provided. */
      $filters = $solarium_query->getFilterQueries();
      // Default date filter key for main search.
      $filter_key = 'filters_2';
      // Metsis Elements view have different filter key for dates.
      if ($this->searchId === 'views_page:metsis_elements__results') {
        $filter_key = 'filters_1';

      }
      // dpm($filters, __FUNCTION__);.
      $date_filter = NULL;
      if (array_key_exists($filter_key, $filters)) {
        $fq = $filters[$filter_key]->getQuery();
        // dpm($fq);
        if (str_contains($fq, 'start_date') && !str_contains($fq, 'end_date')) {
          $date_filter = "START";
        }
        if (str_contains($fq, 'end_date') && !str_contains($fq, 'start_date')) {
          $date_filter = "END";
        }
        if (str_contains($fq, 'start_date') && str_contains($fq, 'end_date')) {
          $date_filter = "STARTEND";
        }
        preg_match_all('(\"\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z\")', $fq, $matches);

        // dpm($date_filter);
        // dpm($matches);
        if (!empty($matches)) {

          if ($date_filter === 'START') {
            $start = $matches[0][0];
            $start_fq = $fq;
            // $new_fq = '(' . $fq . ' AND ';
            $new_fq = '(temporal_extent_start_date:[' . $start . ' TO *])';
            $new_fq .= ' OR ';
            $new_fq .= '(temporal_extent_start_date:[ * TO ' . $start . ']';
            $new_fq .= ' AND temporal_extent_end_date:[' . $start . ' TO *]) OR ';
            $new_fq .= '(' . $start_fq;
            $new_fq .= ' AND (*:* -temporal_extent_end_date:*))';
            $start_fq = $new_fq;
            $filters[$filter_key]->setQuery($new_fq);
          }
          if ($date_filter === 'END') {
            $end = $matches[0][0];
            // $end_fq = $fq;
            // $new_fq = '(' . $fq . ' AND ';
            $new_fq = '(temporal_extent_end_date:[* TO ' . $end . '])';
            $new_fq .= ' OR ';
            $new_fq .= '(temporal_extent_end_date:[' . $end . ' TO *]';
            $new_fq .= ' AND temporal_extent_start_date:[ * TO ' . $end . ']) ';
            // $new_fq .= '(' . $end_fq;
            $new_fq .= ' OR (*:* -temporal_extent_end_date:*)';
            $filters[$filter_key]->setQuery($new_fq);
          }
          if ($date_filter === 'STARTEND') {
            $start = $matches[0][0];
            $end = $matches[0][1];
            $new_fq = '((temporal_extent_start_date:[' . $start . ' TO ' . $end . ']';
            $new_fq .= ' AND temporal_extent_end_date:[' . $start . ' TO ' . $end . '])';
            $new_fq .= ' OR (temporal_extent_start_date:[* TO ' . $start . '] AND -temporal_extent_end_date:[* TO *]))';
            $new_fq .= ' OR ((temporal_extent_start_date:[* TO ' . $start . '] AND temporal_extent_end_date:[' . $end . ' TO *])';
            $new_fq .= ' OR (temporal_extent_start_date:[* TO ' . $start . '] AND -temporal_extent_end_date:[* TO *]))';

            // dpm($new_fq, __FUNCTION__);.
            $filters[$filter_key]->setQuery($new_fq);
          }
          // Else {
          // $rep_str = $matches[0];
          // unset($filters['filters_2']);
          // }.
          $solarium_query->setFilterQueries($filters);
          $solarium_query->addParam('end_date_query', $matches[0]);
          $solarium_query->addParam('okeys', $query->getKeys());

        }

      }

      // dpm($query->getParseMode()->label());
      // dpm($this->config);
      // dpm($this->session);
      // dpm($solarium_query->getFields());
      // dpm("After");
      // dpm($query);
      // dpm($solarium_query);
      // dpm($query->getConditionGroup()->getConditions());
      // $current_query = $solarium_query->getQuery();
      // dpm($current_query, __FUNCTION__);.
      /*
       * Test adding children subquery
       */
      // dpm("Adding children.q");.

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
    $this->getLogger()->notice('postExtractResults');

    if (($this->searchId !== NULL) && (($this->searchId === 'views_page:metsis_search__results'))) {
      // Process the extracted_info for the map given the results.
      $result_set = $event->getSearchApiResultSet();
      $extracted_info = SearchUtils::getExtractedInfoSearchApiResults($result_set);
      $this->metsisState->set('extracted_info', $extracted_info);
      // dpm($this->metsisState->getAll());
    }
  }

  /**
   * Listen to  the post create query.
   *
   * @param \Solarium\Core\Event\PostCreateQuery $event
   *   the current Event.
   */
  public function postCreateQuery(PostCreateQuery $event): void {
    // $this->getLogger()->notice('pstCreateQuery');
    // dpm($event->getQuery());
    // dpm($event, __FUNCTION__);.
  }

  /**
   * Listen to the pre execute query  event.
   *
   * @param \Solarium\Core\Event\PreExecuteRequest $event
   *   The pre execute event.
   */
  public function preExecuteRequest(PreExecuteRequest $event): void {
    // $this->getLogger()->notice('preExecuteRequest');
    // \Drupal::logger('metsis-search')->debug("PreExecuteRequest");
  }

  /**
   * Listen to the post execute query event.
   *
   * @param \Solarium\Core\Event\PostExecuteRequest $event
   *   The post execute event.
   */
  public function postExecuteRequest(PostExecuteRequest $event): void {
    // $this->getLogger()->notice('postExecuteRequest');
    // \Drupal::logger('metsis-search')->debug("PostExecuteRequest");
  }

  /**
   * Listen to the pre create request event.
   *
   * @param \Solarium\Core\Event\PreCreateRequest $event
   *   The pre create request event.
   */
  public function preCreateRequest(PreCreateRequest $event): void {
    // $this->getLogger()->notice('preCreateRequest');
    // dpm("PreCreateRequest");
    // dpm($event->getQuery());
    // dpm($event->getRequest());
    // $query = $event->getQuery();
    // dpm($query->getQuery());
  }

  /**
   * Listen to the post create request event.
   *
   * @param \Solarium\Core\Event\PostCreateRequest $event
   *   The post create request event.
   */
  public function postCreateRequest(PostCreateRequest $event): void {
    // $this->getLogger()->notice('postCreateRequest');
    // dpm("PostCreateRequest");
    // dpm($event->getQuery());
    // $req = $event->getRequest();
    // dpm($req->getParams());
  }

  /**
   * Listen to the post create result event.
   *
   * @param \Solarium\Core\Event\PostCreateResult $event
   *   The post create result event.
   */
  public function postCreateResult(PostCreateResult $event): void {
    $this->getLogger()->notice('postCreateResult');

    // \Drupal::logger('metsis-search')->debug("postCreateResult");
    // dpm($event->getResult());
    if (($this->searchId !== NULL) && (($this->searchId === 'views_page:metsis_search__results'
    || $this->searchId === 'views_page:metsis_elements__results' || $this->searchId === 'views_page:metsis_simple_search__results'))) {
      // dpm($event->getResult(), __FUNCTION__);
      // $result = $event->getSearchApiResultSet();
      // $extracted_info = SearchUtils::getExtractedInfo($event->getResult());
      // $this->session->set('extracted_info', $extracted_info);.
      if ($this->session->has('basket_ref')) {
        $this->session->remove('basket_ref');
      }
    }
  }

  /**
   * Check if a given string is a valid UUID.
   *
   * @param string $uuid
   *   The string to check.
   *
   * @return bool
   *   Return true or false.
   */
  public function isValidUuid($uuid) {
    if (!is_string($uuid) || (preg_match($this->uuidRegexp, $uuid) !== 1)) {
      return FALSE;
    }
    return TRUE;
  }

}

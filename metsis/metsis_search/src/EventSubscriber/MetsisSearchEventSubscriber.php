<?php

namespace Drupal\metsis_search\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;

use Drupal\Component\Utility\Timer;
use Drupal\Core\StringTranslation\StringTranslationTrait;
//use Drupal\devel\DevelDumperManagerInterface;
use Drupal\search_api\LoggerTrait;
use Solarium\Core\Client\Adapter\AdapterHelper;
use Solarium\Core\Event\Events as SolariumEvents;
use Solarium\Core\Event\PostCreateQuery;
use Solarium\Core\Event\PostExecuteRequest;
use Solarium\Core\Event\PreExecuteRequest;
use Solarium\Core\Event\PostCreateResult;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\search_api\Query\QueryInterface;

use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\Core\Cache\CacheBackendInterface;

class MetsisSearchEventSubscriber implements EventSubscriberInterface
{
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

  //Special solr chars
  protected $speacial_chars = ['*','?',':'];

 /**
  * Construct an example service instance.
  *
  * @param \Drupal\Core\Session\AccountProxyInterface $current_user
  *   Account proxy for the currently logged-in user.
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
 }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        $events[SolariumEvents::POST_CREATE_QUERY][] = ['postCreateQuery'];
        $events[SolariumEvents::PRE_EXECUTE_REQUEST][] = ['preExecuteRequest'];
        $events[SolariumEvents::POST_EXECUTE_REQUEST][] = ['postExecuteRequest'];
        $events[SolariumEvents::POST_CREATE_RESULT][] = ['postCreateResult'];
        $events[SearchApiSolrEvents::PRE_QUERY][] = ['onPreQuery'];
        $events[SearchApiSolrEvents::POST_CONVERT_QUERY][] = ['postConvertQuery'];
        return $events;
    }


    /**
     * Listen to  the post convert query event
     *
     * @param \Drupal\search_api_solr\Event\PostConvertedQueryEvent $event
     *
     */
    public function postConvertQuery(PostConvertedQueryEvent $event)
    {
        //  \Drupal::logger('metsis-search')->debug("PostCreateQuery");
      //  dpm($event);
    }



    /**
     * Listen to  the pre create query
     *
     * @param \Drupal\search_api_solr\Event\PreQueryEvent $event
     *
     */
    public function onPreQuery(PreQueryEvent $event)
    {
      //Search api query
      $query = $event->getSearchApiQuery();
      //Solarium search api solr query
      $solarium_query= $event->getSolariumQuery();

      //Get the search id for this search view
      $searchId = $query->getSearchId();
      //Only do something during this event if we have metsis search view
      if($searchId === 'views_page:metsis_search__results') {

        //  \Drupal::logger('metsis-search')->debug("PostCreateQuery");
        /*
        dpm($this->currentUser->id());


        dpm($query->getKeys());
        dpm($query->getSearchId());
        */
        //Get parsemode plugin interface.
        $parse_mode_service = \Drupal::service('plugin.manager.search_api.parse_mode');


        $keys = $query->getKeys();
        $use_direct = false; //Use direct query?

        foreach($keys as $key => $value) {
          if(!is_array($value)) {
        if(preg_match('/[' . preg_quote(implode(',', $this->speacial_chars)) . ']+/', $value)) {
          $use_direct = true;
        }
      }
      else {
        foreach($value as $key => $value2) {
        if(preg_match('/[' . preg_quote(implode(',', $this->speacial_chars)) . ']+/', $value2)) {
          $use_direct = true;
        }
      }
      }
      }
      $conjuction = $query->getParseMode()->getConjunction();
      if($use_direct) {
        $parse_mode = $parse_mode_service->createInstance('direct');
        $parse_mode->setConjunction($conjuction);
        $query->setParseMode($parse_mode);
      }
        //
        //dpm($query->getParseMode()->label());

        //dpm($this->config);
        //dpm($this->session);
      }
    }


    /**
     * Listen to  the post create query
     *
     * @param \Solarium\Core\Event\PostCreateQuery $event
     *
     */
    public function postCreateQuery(PostCreateQuery $event)
    {
        //  \Drupal::logger('metsis-search')->debug("PostCreateQuery");
      //  dpm($event);
    }




    /**
     * Listen to the pre execute query  event.
     *
     * @param \Solarium\Core\Event\PreExecuteRequest $event
     *   The pre execute event.
     */
    public function preExecuteRequest(PreExecuteRequest $event)
    {
        // \Drupal::logger('metsis-search')->debug("PreExecuteRequest");
    }

    /**
     * Listen to the post execute query event
     *
     * @param \Solarium\Core\Event\PostExecuteRequest $event
     *   The post execute event.
     */
    public function postExecuteRequest(PostExecuteRequest $event)
    {
        // \Drupal::logger('metsis-search')->debug("PostExecuteRequest");
    }

    /**
     * Listen to the post create result event.
     *
     * @param \Solarium\Core\Event\PostCreateResult $event
     *   The post create result event.
     */
    public function postCreateResult(PostCreateResult $event)
    {
        //  \Drupal::logger('metsis-search')->debug("postCreateResult");
      //  dpm($event);
    }
}

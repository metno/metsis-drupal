<?php

namespace Drupal\metsis_search\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;

use Drupal\Component\Utility\Timer;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\devel\DevelDumperManagerInterface;
use Drupal\search_api\LoggerTrait;
use Solarium\Core\Client\Adapter\AdapterHelper;
use Solarium\Core\Event\Events as SolariumEvents;
use Solarium\Core\Event\PostCreateQuery;
use Solarium\Core\Event\PostExecuteRequest;
use Solarium\Core\Event\PreExecuteRequest;
use Solarium\Core\Event\PostCreateResult;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MetsisSearchEventSubscriber implements EventSubscriberInterface
{
    use StringTranslationTrait;
    use LoggerTrait;
    /**
     * Constructs a ModuleRouteSubscriber object.
     *
     */
    public function __construct()
    {
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
        return $events;
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
    }
}

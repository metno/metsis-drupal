<?php

namespace Drupal\metsis_lib\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // no_cache for a specific view
    // Drupal::logger('metsis_lib')->debug('entering routesubscriber');.
    /*    if ($route = $collection->get('view.metsis_search.results')) {
    //\Drupal::logger('metsis_lib')->debug('turn of cache metsis search view');

    $route->setOption('no_cache', true);
    }*/
  }

}

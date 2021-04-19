<?php

namespace Drupal\metsis_search\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MetsisSearchEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => ['onEvent', 0]];
  }

  public function onEvent() {
    $request = \Drupal::request();
    $query_from_request = $request->query->all();
    $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
    $referer = $request->headers->get('referer');
    \Drupal::logger('metsis-search')->debug("referer: " .  $referer);
    \Drupal::logger('metsis-search')->debug("params: " . implode(" ", $params));
/* DEPRECTAED    drupal_add_css(drupal_get_path('module', 'metsis_ts_bokeh') . '/css/metsis_ts_bokeh.css'); */
  return;
  }

}

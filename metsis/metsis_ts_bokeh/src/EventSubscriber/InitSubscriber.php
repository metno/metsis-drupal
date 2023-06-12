<?php

namespace Drupal\metsis_ts_bokeh\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribe to events.
 */
class InitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  /**
   * On a partivular event do the following.
   */
  public function onEvent() {
    /* DEPRECTAED    drupal_add_css(drupal_get_path('module', 'metsis_ts_bokeh') . '/css/metsis_ts_bokeh.css'); */
  }

}

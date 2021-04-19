<?php /**
 * @file
 * Contains \Drupal\metsis_ts_bokeh\EventSubscriber\InitSubscriber.
 */

namespace Drupal\metsis_ts_bokeh\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  public function onEvent() {
/* DEPRECTAED    drupal_add_css(drupal_get_path('module', 'metsis_ts_bokeh') . '/css/metsis_ts_bokeh.css'); */
  return;
  }

}

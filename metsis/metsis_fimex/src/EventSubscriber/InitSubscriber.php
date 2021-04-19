<?php /**
 * @file
 * Contains \Drupal\metsis_fimex\EventSubscriber\InitSubscriber.
 */

namespace Drupal\metsis_fimex\EventSubscriber;

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
    drupal_add_css(drupal_get_path('module', 'metsis_wms') . '/css/style.min.css');
    drupal_add_js(drupal_get_path('module', 'metsis_wms') . '/js/bundle.js');
  }

}

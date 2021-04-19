<?php /**
 * @file
 * Contains \Drupal\metsis_csv\EventSubscriber\InitSubscriber.
 */

namespace Drupal\metsis_csv\EventSubscriber;

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
    drupal_add_css(drupal_get_path('module', 'metsis_csv') . '/css/metsis_csv_responsive.css');
  }

}

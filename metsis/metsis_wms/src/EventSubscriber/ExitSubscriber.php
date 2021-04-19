<?php /**
 * @file
 * Contains \Drupal\metsis_wms\EventSubscriber\ExitSubscriber.
 */

namespace Drupal\metsis_wms\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::TERMINATE => ['onEvent', 0]];
  }

  public function onEvent() {

  }

}

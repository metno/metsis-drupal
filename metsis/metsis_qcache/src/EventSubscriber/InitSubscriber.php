<?php /**
 * @file
 * Contains \Drupal\metsis_qcache\EventSubscriber\InitSubscriber.
 */

namespace Drupal\metsis_qcache\EventSubscriber;

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

  }

}

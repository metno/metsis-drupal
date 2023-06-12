<?php

namespace Drupal\metsis_wms\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Terminate event subscriber.
 */
class ExitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::TERMINATE => ['onEvent', 0]];
  }

  /**
   * Handle event.
   */
  public function onEvent() {

  }

}

<?php

namespace Drupal\crud_event\EventSubscriber;

use Drupal\crud_event\CRUD;
use Drupal\crud_event\Event\CRUDEvent;

/**
 * Entity delete event event subscriber base.
 */
class DeleteSubscriber extends DeleteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CRUD::DELETE][] = ['onEntityDelete', 800];
    return $events;
  }

  /**
   * Method called when entity is delete.
   *
   * @param \Drupal\crud_event\Event\CRUDEvent $crud_event
   *   The event.
   */
  public function onEntityDelete(CRUDEvent $crud_event) {
    dump($crud_event);
    exit;
  }

}

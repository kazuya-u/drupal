<?php

namespace Drupal\crud_event\EventSubscriber;

use Drupal\crud_event\CRUD;
use Drupal\crud_event\Event\CRUDEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Entity delete event event subscriber base.
 */
abstract class DeleteSubscriberBase implements EventSubscriberInterface {

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
  abstract public function onEntityDelete(CRUDEvent $crud_event);

}

<?php

namespace Drupal\crud_event\EventSubscriber;

use Drupal\crud_event\CRUD;
use Drupal\crud_event\Event\CRUDEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Entity update event event subscriber base.
 */
abstract class UpdateSubscriberBase implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CRUD::UPDATE][] = ['onEntityUpdate', 800];
    return $events;
  }

  /**
   * Method called when entity is update.
   *
   * @param \Drupal\crud_event\Event\CRUDEvent $crud_event
   *   The event.
   */
  abstract public function onEntityUpdate(CRUDEvent $crud_event);

}

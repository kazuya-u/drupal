<?php

namespace Drupal\crud_event\EventSubscriber;

use Drupal\crud_event\CRUD;
use Drupal\crud_event\Event\CRUDEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Entity create event event subscriber base.
 */
abstract class CreateSubscriberBase implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CRUD::CREATE][] = ['onEntityCreate', 800];
    return $events;
  }

  /**
   * Method called when entity is create.
   *
   * @param \Drupal\crud_event\Event\CRUDEvent $crud_event
   *   The event.
   */
  abstract public function onEntityCreate(CRUDEvent $crud_event);

}

<?php

namespace Drupal\crud_event\EventSubscriber;

use Drupal\crud_event\CRUD;
use Drupal\crud_event\Event\CRUDEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Entity crud event event subscriber base.
 */
abstract class PresaveSubscriberBase implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CRUD::PRESAVE][] = ['onEntityPresave', 800];
    return $events;
  }

  /**
   * Method called when an entity is presaved.
   *
   * @param \Drupal\crud_event\Event\CRUDEvent $crud_event
   *   The event.
   */
  abstract public function onEntityPresave(CRUDEvent $crud_event);

}

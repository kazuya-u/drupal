<?php

declare(strict_types=1);

namespace Drupal\crud_event\EventSubscriber;

use Drupal\crud_event\CRUD;
use Drupal\crud_event\Event\CRUDEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a base class to CRUD EventSubscriber.
 */
abstract class CRUDSubscriberBase implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CRUD::CREATE][] = ['onEntityOperate', 800];
    $events[CRUD::UPDATE][] = ['onEntityOperate', 800];
    $events[CRUD::DELETE][] = ['onEntityOperate', 800];
    $events[CRUD::PRESAVE][] = ['onEntityOperate', 800];
    return $events;
  }

  /**
   * This method is called when a CRUD event occurs.
   *
   * Utilized to define specific logic for event subscribers during entity CRUD
   * operations (create, read, update, delete, and pre-save). Subclasses must
   * implement this abstract method to execute appropriate actions for CRUD
   * events.
   *
   * @param \Drupal\crud_event\Event\CRUDEvent $crud_event
   *   Event object with CRUD event details. It allows access to the operated
   *   entity instances and event type (create, read, update, delete, pre-save).
   */
  abstract public function onEntityOperate(CRUDEvent $crud_event);

  /**
   * Method called when entity is created.
   *
   * @param \Drupal\crud_event\Event\CRUDEvent $crud_event
   *   The event.
   */
  protected function onEntityCreate(CRUDEvent $crud_event) {}

  /**
   * Method called when entity is updated.
   *
   * @param \Drupal\crud_event\Event\CRUDEvent $crud_event
   *   The event.
   */
  protected function onEntityUpdate(CRUDEvent $crud_event) {}

  /**
   * Method called when entity is deleted.
   *
   * @param \Drupal\crud_event\Event\CRUDEvent $crud_event
   *   The event.
   */
  protected function onEntityDelete(CRUDEvent $crud_event) {}

  /**
   * Method called when entity is presave.
   *
   * @param \Drupal\crud_event\Event\CRUDEvent $crud_event
   *   The event.
   */
  protected function onEntityPresave(CRUDEvent $crud_event) {}

}

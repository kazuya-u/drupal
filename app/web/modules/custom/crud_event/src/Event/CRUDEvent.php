<?php

namespace Drupal\crud_event\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Entity\EntityInterface;

/**
 * Define the entity CRUD event.
 */
final class CRUDEvent extends Event {

  /**
   * The Entity.
   */
  protected EntityInterface $entity;

  /**
   * The event type.
   */
  protected string $eventType;

  /**
   * Construct the CRUD event for target entity.
   *
   * @param string $event_type
   *   The event type.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity during the occurrence of an event.
   */
  public function __construct(
    string $event_type,
    EntityInterface $entity
  ) {
    $this->eventType = $event_type;
    $this->entity = $entity;
  }

  /**
   * Method to get the entity from the event.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   An entity object.
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

  /**
   * Method to get the event type.
   *
   * @return string
   *   The CRUD type.
   */
  public function getEventType(): string {
    return $this->eventType;
  }

}

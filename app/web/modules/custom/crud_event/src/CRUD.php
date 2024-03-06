<?php

declare(strict_types=1);

namespace Drupal\crud_event;

/**
 * Provides Helper method.
 */
final class CRUD {

  /**
   * The event name - CREATE.
   *
   * @var string
   */
  const CREATE = 'event.create';

  /**
   * The event name - READ.
   *
   * @var string
   */
  const READ = 'event.read';

  /**
   * The event name - UPDATE.
   *
   * @var string
   */
  const UPDATE = 'event.update';

  /**
   * The event name - UPDATE.
   *
   * @var string
   */
  const DELETE = 'event.delete';

  /**
   * The event name - Presave.
   *
   * @var string
   */
  const PRESAVE = 'event.presave';

}

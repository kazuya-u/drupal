<?php

namespace Drupal\custom_jmeter_boot_manager\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Jmeter Boot Manager Service.
 */
class JMeterBootManagerService {

  /**
   * The Number of JMeter Servers.
   *
   * @var int
   */
  const NUMBR_OF_JMETER_SERVERS = 2;

  /**
   * The Save Base Directory.
   *
   * @var string
   */
  const SAVE_BASE_DIRECTORY = 'private://aws/jmeter';

  /**
   * The Save Directories.
   *
   * @var array
   */
  const SAVE_DIRECTORIES = [
    'action' => self::SAVE_BASE_DIRECTORY . '/ec2/action/',
    'flag' => self::SAVE_BASE_DIRECTORY . '/ec2/flag/',
  ];

  /**
   * The File Extension.
   *
   * @var string
   */
  const FILE_EXTENSION = '.txt';

  /**
   * Timezone.
   *
   * @var string
   */
  const TIMEZONE = 'Asia/Tokyo';

  /**
   * The Current User.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  public $currentUser;

  /**
   * The File System.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  public $fileSystem;

  /**
   * The Messenger.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  public $messenger;

  /**
   * The Translation.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  public $translationManager;

  /**
   * Constructs.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   */
  public function __construct(
    AccountProxyInterface $current_user,
    FileSystemInterface $file_system,
    MessengerInterface $messenger,
    TranslationInterface $string_translation,
  ) {
    $this->currentUser = $current_user;
    $this->fileSystem = $file_system;
    $this->messenger = $messenger;
    $this->translationManager = $string_translation;
  }

  /**
   * Installation.
   */
  public function installation(): void {
    $this->prepareDirectory();
  }

  /**
   * Uninstallation.
   */
  public function uninstallation(): void {
    $this->cleanUpDirectory();
  }

  /**
   * Prepare directory.
   */
  public function prepareDirectory(): void {
    foreach (self::SAVE_DIRECTORIES as $uri) {
      $this->fileSystem->prepareDirectory($uri, FileSystemInterface::CREATE_DIRECTORY);
    }
  }

  /**
   * Clean-up directory.
   */
  public function cleanUpDirectory(): void {
    $this->fileSystem->deleteRecursive(self::SAVE_BASE_DIRECTORY);
  }

  /**
   * Get JMeter Servers.
   */
  public function getServers(): array {
    return array_map(fn ($i) => sprintf('jmeter%02d', $i), range(1, self::NUMBR_OF_JMETER_SERVERS));
  }

  /**
   * File exists.
   */
  public function fileExists(string $type, string $server): bool {
    if (!empty(self::SAVE_DIRECTORIES[$type])) {
      return file_exists(self::SAVE_DIRECTORIES[$type] . $server . self::FILE_EXTENSION);
    }
    return FALSE;
  }

  /**
   * Load file.
   */
  public function loadFile(string $type, string $server): string {
    if ($this->fileExists($type, $server)) {
      return @file_get_contents(self::SAVE_DIRECTORIES[$type] . $server . self::FILE_EXTENSION) ?: '';
    }
    return '';
  }

  /**
   * Save file.
   */
  public function saveFile(string $type, string $server, string $data = ''): bool {
    $this->prepareDirectory();
    return $this->fileSystem->saveData($data, self::SAVE_DIRECTORIES[$type] . $server . self::FILE_EXTENSION, FileSystemInterface::EXISTS_REPLACE) ? TRUE : FALSE;
  }

  /**
   * Delete file.
   */
  public function deleteFile(string $type, string $server): bool {
    $this->fileSystem->delete(self::SAVE_DIRECTORIES[$type] . $server . self::FILE_EXTENSION);
    return !$this->fileExists($type, $server);
  }

  /**
   * Exists action.
   */
  public function actionExists(string $server): bool {
    return $this->fileExists('action', $server);
  }

  /**
   * Load action.
   */
  public function loadAction(string $server): string {
    return $this->loadFile('action', $server);
  }

  /**
   * Save action.
   */
  public function saveAction(string $server, string $data): bool {
    return $this->saveFile('action', $server, $data);
  }

  /**
   * Delete action.
   */
  public function deleteAction(string $server): bool {
    return $this->deleteFile('action', $server);
  }

  /**
   * Exists flag.
   */
  public function flagExists(string $server): bool {
    return $this->fileExists('flag', $server);
  }

  /**
   * Load flag.
   */
  public function loadFlag(string $server): string {
    return $this->loadFile('flag', $server);
  }

  /**
   * Save flag.
   */
  public function saveFlag(string $server, string $data): bool {
    return $this->saveFile('flag', $server, $data);
  }

  /**
   * Delete flag.
   */
  public function deleteFlag(string $server): bool {
    return $this->deleteFile('flag', $server);
  }

  /**
   * Messenger.
   */
  public function messenger($message, array $args = [], $type = MessengerInterface::TYPE_STATUS): MessengerInterface {
    return $this->messenger->addMessage($this->translationManager->translate(ucfirst(strip_tags($message)), $args), $type);
  }

}

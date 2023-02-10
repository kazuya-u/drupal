<?php

namespace Drupal\custom_jmeter_boot_manager\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Jmeter Boot Manager Service.
 */
class JmeterBootManagerService {

  /**
   * The Number of Jmeter Service.
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
   * @var Drupal\Core\Messenger\Messenger
   */
  public $messenger;

  /**
   * The Translation.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  public $translationManager;

  /**
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *  The current user.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *  The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *  The string translation.
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
   * Get Jmeter Server.
   */
  public function getServers(): array {
    return array_map(fn ($i) => sprintf('jmeter%02d', $i), range(1, self::NUMBR_OF_JMETER_SERVERS));
  }

  /**
   * Prepare directory.
   */
  public function prepareDirectory(): void
  {
    foreach (self::SAVE_DIRECTORIES as $uri) {
      $this->fileSystem->prepareDirectory($uri, FileSystemInterface::CREATE_DIRECTORY);
    }
  }
  /**
   * Installation.
   */
  public function installation():void {
    $this->prepareDirectory();
  }

  /**
   * Clean-up directory.
   */
  public function cleanUpDirectory(): void {
    $this->fileSystem->deleteRecursive(self::SAVE_BASE_DIRECTORY);
  }

  /**
   * File exists.
   */
  public function fileExists(string $type, string $server_name): bool {
    if (!empty(self::SAVE_DIRECTORIES[$type])) {
      return file_exists(self::SAVE_DIRECTORIES[$type] . $server_name . self::FILE_EXTENSION);
    }
    return FALSE;
  }

  /**
   * Exists action.
   */
  public function actionExists(string $server_name): bool {
    return $this->fileExists('action', $server_name);
  }

  /**
   * Exists flag.
   */
  public function flagExists(string $server_name): bool {
    return $this->fileExists('flag', $server_name);
  }

  /**
   * Load file.
   */
  public function loadFile(string $type, string $server_name): string {
    if ($this->fileExists($type, $server_name)) {
      return @file_get_contents(self::SAVE_DIRECTORIES[$type] . $server_name . self::FILE_EXTENSION) ?: '';
    }
    return '';
  }

  /**
   * Load action.
   */
  public function loadAction(string $server_name): string {
    return $this->loadFile('action', $server_name);
  }

  /**
   * Save file.
   */
  public function saveFile(string $type, string $server_name, string $data = ''): bool {
    return $this->fileSystem->saveData($data, self::SAVE_DIRECTORIES[$type] . $server_name . self::FILE_EXTENSION, FileSystemInterface::EXISTS_REPLACE) ? TRUE : FALSE;
  }

  /**
   * Save action.
   */
  public function saveAction(string $server_name, string $data) {
    return $this->saveFile('action', $server_name, $data);
  }

  /**
   * Save flag.
   */
  public function saveFlag(string $server_name) {
    return $this->saveFile('flag', $server_name, '');
  }

  /**
   * Delete file.
   */
  public function deleteFile(string $type, string $server_name) {
    return $this->fileSystem->delete(self::SAVE_DIRECTORIES[$type] . $server_name . self::FILE_EXTENSION) ? TRUE : FALSE;
  }

  /**
   * Delete action.
   */
  public function deleteAction(string $server_name) {
    return $this->deleteFile('action', $server_name);
  }

  /**
   * Messenger.
   */
  public function messenger($message, array $args = [], $type = MessengerInterface::TYPE_STATUS): MessengerInterface {
    return $this->messenger->addMessage($this->translationManager->translate(ucfirst(strip_tags($message)), $args), $type);
  }
}

<?php

namespace Drupal\custom_jmeter_boot_manager\Service;

use DateTime;
use Drupal\Core\Datetime\DrupalDateTime;
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
   * The Server Shutdown Time.
   * @var int
   */
  const SERVER_SHUTDOWN_TIME = 12;

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
  public static function getServers(): array {
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
   * Clean-up directory.
   *
   */
  public function cleanUpDirectory($path): void {
    $this->fileSystem->deleteRecursive($path);
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
  public function uninstallation(): void {
    $this->cleanUpDirectory(self::SAVE_BASE_DIRECTORY);
  }

  // /**
  //  * Clean-up action directory.
  //  */
  // public function cleanUpAction(): void {
  //   $this->cleanUpDirectory(self::SAVE_DIRECTORIES['action']);
  // }

  /**
   * File exists.
   */
  public static function fileExists(string $type, string $server_name): bool {
    if (!empty(self::SAVE_DIRECTORIES[$type])) {
      return file_exists(self::SAVE_DIRECTORIES[$type] . $server_name . self::FILE_EXTENSION);
    }
    return FALSE;
  }

  /**
   * Exists action.
   */
  public static function actionExists(string $server_name): bool {
    return self::fileExists('action', $server_name);
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
  public static function deleteFile(string $type, string $server_name): bool {
    return \Drupal::service('file_system')->delete(self::SAVE_DIRECTORIES[$type] . $server_name . self::FILE_EXTENSION) ? TRUE : FALSE;
  }

  /**
   * Delete action.
   */
  public static function deleteAction(string $server_name) {
    return self::deleteFile('action', $server_name);
  }


  /**
   * Get createtime.
   */
  public static function getCreatetime(string $type, string $server_name): string{
    if (self::fileExists($type, $server_name)) {
      return @filectime(self::SAVE_DIRECTORIES[$type] . $server_name . self::FILE_EXTENSION) ?: '';
    }
    return '';
  }

  // /**
  //  * Get Actiontime.
  //  */
  // public static function getActionCreate(string $server_name): string {
  //   return self::getCreatetime('action', $server_name);
  // }

  /**
   * Get Actiontime.
   */
  public static function getActionCreate(string $server_name): DateTime {
    $create_time = self::getCreatetime('action', $server_name);
    return (new DateTime())->setTimestamp($create_time);
  }

  /**
   * Flag for server downtime elapse.
   */
  public function flagDownTime(string $server_name): bool {
    $current_time = new DrupalDateTime('now');
    return $current_time->diff(self::getActionCreate($server_name))->h > 12 ? TRUE : FALSE;
  }

  /**
   * Messenger.
   */
  public function messenger($message, array $args = [], $type = MessengerInterface::TYPE_STATUS): MessengerInterface {
    return $this->messenger->addMessage($this->translationManager->translate(ucfirst(strip_tags($message)), $args), $type);
  }
}

<?php

namespace Drupal\custom_jmeter_boot_manager\Service;

use Drupal\Core\File\FileSystemInterface;

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
   * The File System.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  public $fileSystem;

  /**
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(
    FileSystemInterface $file_system,
  ) {
    $this->fileSystem = $file_system;
  }

  /**
   * Get Jmeter Server.
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
   * Exists action.
   */
  public function actionExists(string $server): bool {
    return $this->fileExists('action', $server);
  }
}

<?php

namespace Drupal\custom_jmeter_boot_manager\Form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\custom_jmeter_boot_manager\Service\JmeterBootManagerService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Jmter Boot Manager Form class.
 */
class JmeterBootManagerForm extends FormBase {

  /**
   * @var Drupal\custom_jmeter_boot_manager\Service\JmeterBootManagerService
   */
  protected $JmeterBootManagerService;

  /**
   * Constructs.
   *
   * @param Drupal\custom_jmeter_boot_manager\Service\JmeterBootManagerService $jmeter_boot_manager
   */
  public function __construct(
    JmeterBootManagerService $jmeter_boot_manager,
  ) {
    $this->JmeterBootManagerService = $jmeter_boot_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('jmeter_boot_manager')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId()
  {
    return 'jmeter_boot_manager';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    foreach ($this->JmeterBootManagerService->getServers() as $jmeter) {
      $form[$jmeter] = [
        '#type' => 'fieldset',
        '#title' => $jmeter,
        'start_stop' => [
          '#type' => 'submit',
          '#name' => $jmeter,
          '#value' => $this->JmeterBootManagerService->actionExists($jmeter) ? 'Stop' : 'Start',
          // '#disabled' =>
        ],
      ];
    }
    exit;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getUserInput();
    $server = '';
    $jmeter_servers = array_map(fn ($i) => sprintf('jmeter%02d', $i), range(1, self::NUMBR_OF_JMETER_SERVERS));
    foreach ($jmeter_servers as $jmeter) {
      if ($values[$jmeter] ?? '') {
        $server = $jmeter;
        break;
      }
    }
    // kint($form_state);
    if ($server === '') {
      $form_state->setError($form, 'Server operations not managed by a JMeter Boot Manager are prohibited.');
    }
  }

    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getUserInput();
    $server = '';
    $direction = '';
    $jmeter_servers = array_map(fn ($i) => sprintf('jmeter%02d', $i), range(1, self::NUMBR_OF_JMETER_SERVERS));
    foreach ($jmeter_servers as $jmeter) {
      if ($direction = $values[$jmeter] ?? '') {
        $server = $jmeter;
        break;
      }
    }
    kint($direction);
    if ($server === '') {
      return;
    }
    $message_type = '';
    $message_string = '';
    switch ($direction) {
      case 'Start':
        $message_string = 'Start by __ at __.';
        if ($this->JmeterBootManager->saveData('umeki', self::SAVE_DIRECTORIES['action'] . $server . self::FILE_EXTENSION, FileSystemInterface::EXISTS_REPLACE)) {
          // no script.
          kint('fileが書き換えられたよ');
        }
        else {
          $message_type = 'ファイル操作ミス';
        }
        break;
      case 'Stop':
        $message_string = 'Stop by __ at __.';
        if ($this->JmeterBootManager->delete(self::SAVE_DIRECTORIES['action'] . $server . self::FILE_EXTENSION)) {
          // no script.
          kint('ファイルの滅却。');
        }
        else {
          $message_type = 'ファイル操作ミス';
        }
        break;
    }
    if ($message_type === 'ファイル操作ミス') {
      $message_string = 'ミス';
    }
    else {
      $message_string = 'jmeter01' . $message_string;
    }
    $this->messenger()->addStatus($message_string);
  }
}

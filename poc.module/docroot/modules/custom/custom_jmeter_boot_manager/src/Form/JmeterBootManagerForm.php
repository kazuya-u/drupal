<?php

namespace Drupal\custom_jmeter_boot_manager\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
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
   *  The jmeter boot manager service.
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
        '#markup' => $this->JmeterBootManagerService->loadAction($jmeter),
        'start_stop' => [
          '#type' => 'submit',
          '#name' => $jmeter,
          '#value' => $this->JmeterBootManagerService->actionExists($jmeter) ? 'Stop' : 'Start',
          '#disabled' => $this->JmeterBootManagerService->flagExists($jmeter),
        ],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getUserInput();
    $server = '';
    foreach ($this->JmeterBootManagerService->getServers() as $jmeter) {
      if ($values[$jmeter] ?? '') {
        $server = $jmeter;
        break;
      }
    }
    if ($server === '') {
      $form_state->setError($form, 'Server operations not managed by a JMeter Boot Manager are prohibited.');
    }
  }

    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getValues();
    $server = '';
    $direction = '';
    foreach ($this->JmeterBootManagerService->getServers() as $jmeter) {
      if ($direction = $values[$jmeter] ?? '') {
        $server = $jmeter;
        break;
      }
    }
    if ($server === '') {
      return;
    }
    $datetime = new DrupalDateTime('now', JmeterBootManagerService::TIMEZONE);
    $message_string = '';
    $message_args = [
      '@user_name' => $this->JmeterBootManagerService->currentUser->getDisplayName(),
      '@datetime' => $datetime->format('Y-m-d H:i:s e'),
    ];
    $message_type = MessengerInterface::TYPE_STATUS;
    switch ($direction) {
      case 'Start':
        $message_string = '<p>Start by @user_name at @datetime.</p>';
        if ($this->JmeterBootManagerService->saveAction($server, strtr($message_string, $message_args)) && $this->JmeterBootManagerService->saveFlag($jmeter)) {
          // no script.
        }
        else {
          $message_type = MessengerInterface::TYPE_ERROR;
        }
        break;
      case 'Stop':
        $message_string = '<p>Stop by @user_name at @datetime.</p>';
        if ($this->JmeterBootManagerService->deleteAction($server) && $this->JmeterBootManagerService->saveFlag($jmeter)) {
          // no script.
        }
        else {
          $message_type = MessengerInterface::TYPE_ERROR;
        }
        break;
    }
    if ($message_type === MessengerInterface::TYPE_ERROR) {
      $message_string = '<p><em>An unexpected problem has occurred';
      $message_string_end = '.</em></p>';
      $this->JmeterBootManagerService->saveAction($server, $message_string . $message_string_end);
      $message_string .= ' on the @jmeter' . $message_string_end;
      $message_args = ['@jmeter' => $jmeter];
    }
    else {
      $message_string = strtolower($jmeter . ' ' . $message_string);
    }
    $this->JmeterBootManagerService->messenger($message_string, $message_args, $message_type);
  }
}

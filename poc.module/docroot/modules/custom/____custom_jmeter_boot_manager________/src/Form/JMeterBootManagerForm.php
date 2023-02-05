<?php

namespace Drupal\custom_jmeter_boot_manager\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\custom_jmeter_boot_manager\Service\JMeterBootManagerService as JMeterBootManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * JMeter Boot Manager Form class.
 */
class JMeterBootManagerForm extends FormBase {

  /**
   * The JMeter Boot Manager.
   *
   * @var \Drupal\custom_jmeter_boot_manager\Service\JMeterBootManagerService
   */
  protected $jMeterBootManager;

  /**
   * Constructs.
   *
   * @param \Drupal\custom_jmeter_boot_manager\Service\JMeterBootManagerService $jmeter_boot_manager
   *   The jmeter boot manager.
   */
  public function __construct(
    JMeterBootManager $jmeter_boot_manager,
  ) {
    $this->jMeterBootManager = $jmeter_boot_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('jmeter_boot_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'jmeter_boot_manager';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    foreach ($this->jMeterBootManager->getServers() as $jmeter) {
      $form[$jmeter] = [
        '#type' => 'fieldset',
        '#title' => $jmeter,
        '#markup' => $this->jMeterBootManager->loadAction($jmeter),
        'start_stop' => [
          '#type' => 'submit',
          '#name' => $jmeter,
          '#value' => $this->jMeterBootManager->actionExists($jmeter) ? 'Stop' : 'Start',
          '#disabled' => $this->jMeterBootManager->flagExists($jmeter),
        ],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getUserInput();
    $server = '';
    foreach ($this->jMeterBootManager->getServers() as $jmeter) {
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $server = '';
    $direction = '';
    foreach ($this->jMeterBootManager->getServers() as $jmeter) {
      if ($direction = $values[$jmeter] ?? '') {
        $server = $jmeter;
        break;
      }
    }
    if ($server === '') {
      return;
    }
    $datetime = new DrupalDateTime('now', JMeterBootManager::TIMEZONE);
    $message_string = '';
    $message_args = [
      '@user_name' => $this->jMeterBootManager->currentUser->getDisplayName(),
      '@datetime' => $datetime->format('Y-m-d H:i:s e'),
    ];
    $message_type = MessengerInterface::TYPE_STATUS;
    switch ($direction) {
      case 'Start':
        $message_string = '<p>Started by @user_name at @datetime.</p>';
        if (!$this->jMeterBootManager->saveAction($server, strtr($message_string, $message_args))
          || !$this->jMeterBootManager->saveFlag($server, $datetime->getTimestamp())
        ) {
          $message_type = MessengerInterface::TYPE_ERROR;
        }
        break;

      case 'Stop':
        $message_string = '<p>Stopped by @user_name at @datetime.</p>';
        if (!$this->jMeterBootManager->deleteAction($server)
          || !$this->jMeterBootManager->saveFlag($server, $datetime->getTimestamp())
        ) {
          $message_type = MessengerInterface::TYPE_ERROR;
        }
        break;
    }
    if ($message_type === MessengerInterface::TYPE_ERROR) {
      $message_string = '<p><em>An unexpected problem has occurred';
      $message_string_end = '.</em></p>';
      $this->jMeterBootManager->saveAction($server, $message_string . $message_string_end);
      $message_string .= ' on the @jmeter' . $message_string_end;
      $message_args = ['@jmeter' => $jmeter];
    }
    else {
      $message_string = strtolower($jmeter . ' ' . $message_string);
    }
    $this->jMeterBootManager->messenger($message_string, $message_args, $message_type);
  }

}

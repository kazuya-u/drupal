<?php

namespace Drupal\jmeter\Form;

use Drupal;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Psr\Container\ContainerInterface;
class JmeterForm extends FormBase {

  /**
   *
   * @var Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Constructs.
   *
   * @param Drupal\Core\File\FileSystem $fileSystemInterface
   */
  public function __construct(FileSystemInterface $fileSystemInterface) {
    $this->fileSystem = $fileSystemInterface;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system')
    );
  }

  const JMETER_SERVER = [
    'jmeter01',
    'jmeter02',
  ];

  /**
   * {@inheritDoc}
   */
  public function getFormId()
  {
    return 'jmeter_toggle';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $file_dir = '/app/config/jmeter/';
    $user_dir = glob('/app/config/jmeter/user/*');
    foreach ($user_dir as $user_file) {
      $user_file = preg_replace("/(.+)(\.[^.]+$)/", "$1", $user_file);
      $user_file = explode('/', $user_file);
      $user_file = end($user_file);
      $user_file =  str_replace('_' , ' ', $user_file);
      if (preg_match('/^(?<server>[^\s]+) (?<mode>[^\s]+)$/', $user_file, $matches)) {
        $user_info[$matches['server']] = $matches['mode'];
      }
    }
    foreach(self::JMETER_SERVER as $server) {
      if (!empty($user_info) && array_key_exists($server, $user_info)) {
        $user_name = $user_info[$server];
      } else {
        $user_name = '';
      }
      $form[$server . 'status'] = [
        '#type' => 'textfield',
        '#value' => file_exists($file_dir . $server . '.txt') ? $user_name . 'が' . $server . '起動中。' : $server . '停止中。',
        '#disabled' => true,
      ];
      $form[$server . 'start'] = [
        '#type' => 'submit',
        '#value' => $server . ' Start',
        '#disabled' => file_exists($file_dir . $server . '.txt'),
      ];
      $form[$server . 'stop'] = [
        '#type' => 'submit',
        '#value' => $server . ' Stop',
        '#disabled' => !file_exists($file_dir . $server . '.txt'),
      ];
    }
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $user_name = \Drupal::currentUser()->getDisplayName();
    $file_name = '';
    $form_input = $form_state->getUserInput();
    $form_input_op = $form_input['op'];
    $jmeter_pressed = preg_split('/\s/', $form_input_op);
    // 押したボタン名が、配列のなかにその配列名があるかのエラー処理。
    if (!in_array($jmeter_pressed['0'], self::JMETER_SERVER)) return;
    $file_name = '/app/config/jmeter/' . $jmeter_pressed['0'] . '.txt';
    $user_file_name = '/app/config/jmeter/user/' . $jmeter_pressed['0'] . '_' . $user_name . '.txt';
    if (file_exists($file_name)) {
      \Drupal::service('file_system')->unlink($file_name);
      \Drupal::service('file_system')->unlink($user_file_name);
      $this->messenger()->addStatus($this->t($jmeter_pressed['0'] . 'が停止しました。'));
    } else {
      touch($file_name);
      touch($user_file_name);
      $this->messenger()->addStatus($this->t($jmeter_pressed['0'] . 'が起動しました。 - 使用者：' . $user_name));
    }
  }
}

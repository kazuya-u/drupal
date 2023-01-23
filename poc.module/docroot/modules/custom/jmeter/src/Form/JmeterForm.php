<?php

namespace Drupal\jmeter\Form;

use DateTime;
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
    $currentDateTime = new DateTime();
    $currentDateTime = strtotime($currentDateTime->format('H:i:s'));
    $file_dir = '/app/config/jmeter/';
    $user_dir = glob('/app/config/jmeter/user/*');
    foreach ($user_dir as $user_file) {
      $user_file = preg_replace("/(.+)(\.[^.]+$)/", "$1", $user_file);
      $user_file = explode('/', $user_file);
      $user_file = end($user_file);
      $user_file =  str_replace('_' , ' ', $user_file);
      if (preg_match('/^(?<server>[^\s]+) (?<user>[^\s]+) (?<submitTime>[^\s]+)$/', $user_file, $matches)) {
        $user_info[$matches['server']] = $matches['user'];
        $user_info[$matches['server'] . $matches['server']] = $matches['submitTime'];
      }
    }
    foreach(self::JMETER_SERVER as $server) {
      if (!empty($user_info) && array_key_exists($server, $user_info)) {
        $user_name = $user_info[$server];
        $submitDateTime = strtotime($user_info[$server . $server]);
        $diff = $currentDateTime - $submitDateTime;
      } else {
        $user_name = '';
        $diff = '301';
      }
      // kint($diff);

      // !$diff > 300ならボタン無効。
      $form[$server . 'status'] = [
        '#type' => 'textfield',
        '#value' => file_exists($file_dir . $server . '.txt') ? $user_name . 'が' . $server . '起動中。' : $server . '停止中。',
        '#disabled' => true,
      ];
      if ($diff > 10) {
        $form[$server . 'start'] = [
          '#type' => 'submit',
          '#value' => $server . ' Start',
          // '#disabled' => $diff > 300 && file_exists($file_dir . $server . '.txt') ? false : true,
          '#disabled' => file_exists($file_dir . $server . '.txt'),
        ];
        $form[$server . 'stop'] = [
          '#type' => 'submit',
          '#value' => $server . ' Stop',
          '#disabled' => !file_exists($file_dir . $server . '.txt'),
        ];
      } else {
        $form[$server . 'start'] = [
          '#type' => 'submit',
          '#value' => $server . ' Start',
          // '#disabled' => $diff > 300 && file_exists($file_dir . $server . '.txt') ? false : true,
          '#disabled' => true,
        ];
        $form[$server . 'stop'] = [
          '#type' => 'submit',
          '#value' => $server . ' Stop',
          '#disabled' => true,
        ];
      }

    }
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $user_name = \Drupal::currentUser()->getDisplayName();
    $submitDateTime = new DateTime();
    $file_name = '';
    $form_input = $form_state->getUserInput();
    $form_input_op = $form_input['op'];
    $jmeter_pressed = preg_split('/\s/', $form_input_op);
    // 押したボタン名が、配列のなかにその配列名があるかのエラー処理。
    if (!in_array($jmeter_pressed['0'], self::JMETER_SERVER)) return;
    $file_name = '/app/config/jmeter/' . $jmeter_pressed['0'] . '.txt';
    $user_file_name = '/app/config/jmeter/user/' . $jmeter_pressed['0'] . '_' . $user_name . '_' . $submitDateTime->format('H:i:s') . '.txt';
    if (file_exists($file_name)) {
      \Drupal::service('file_system')->unlink($file_name);
      $user_dir = glob('/app/config/jmeter/user/*');
      foreach ($user_dir as $user_file) {
        if (!strpos($user_file, $jmeter_pressed['0'])) continue;
        $user_file_name = $user_file;
        \Drupal::service('file_system')->unlink($user_file_name);
        $this->messenger()->addStatus($this->t($jmeter_pressed['0'] . 'が停止しました。'));
      }
    } else {
      touch($file_name);
      touch($user_file_name);
      $this->messenger()->addStatus($this->t($jmeter_pressed['0'] . 'が起動しました。 - 使用者：' . $user_name));
    }
  }
}

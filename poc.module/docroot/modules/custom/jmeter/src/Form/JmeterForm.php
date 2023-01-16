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
    foreach(self::JMETER_SERVER as $server) {
      $form[$server . 'start'] = [
        '#type' => 'submit',
        '#value' => $server . ' Start',
        '#disabled' => file_exists('/app/config/jmeter/' . $server . '.txt'),
      ];
      $form[$server . 'stop'] = [
        '#type' => 'submit',
        '#value' => $server . ' Stop',
        '#disabled' => !file_exists('/app/config/jmeter/' . $server . '.txt'),
      ];
    }
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $file_name = '';
    $form_input = $form_state->getUserInput();
    $form_input_op = $form_input['op'];
    $jmeter_pressed = preg_split('/\s/', $form_input_op);
    // 押したボタン名が、配列のなかにその配列名があるかのエラー処理。
    if (!in_array($jmeter_pressed['0'], self::JMETER_SERVER)) return;
    $file_name = '/app/config/jmeter/' . $jmeter_pressed['0'] . '.txt';
    file_exists($file_name) ? \Drupal::service('file_system')->unlink($file_name) : touch($file_name);
    $this->messenger()->addStatus(
      file_exists($file_name) ? $this->t($jmeter_pressed['0'] . 'が起動しました。') : $this->t($jmeter_pressed['0'] . 'が停止しました。')
    );
    // $subjectは入力する文字だから、どのボタンを押したかが入っている。例：「Jmater1 Start」
    // //preb_matchは、入力されたボタンの文字を区切るためのものっていう認識。
    // if (preg_match('/[\s]/', $form_input_op, $matches)) {
    //   kint($matches);
    //   exit;
    // }
  }
}
